<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
use Zobay\LaravelSslCommerz\DTOs\PaymentRequest;
use Zobay\LaravelSslCommerz\DTOs\PaymentSession;
use Zobay\LaravelSslCommerz\DTOs\ValidationResult;
use Zobay\LaravelSslCommerz\Exceptions\OrderValidationException;
use Zobay\LaravelSslCommerz\Exceptions\PaymentInitiationException;

class LaravelSslCommerz
{
    private string $storeId;
    private string $storePassword;

    public function __construct()
    {
        $this->storeId       = config('sslcommerz.credentials.store_id');
        $this->storePassword = config('sslcommerz.credentials.store_passwd');
    }

    public function initiatePayment(PaymentRequest $request): PaymentSession
    {
        $payload = array_merge($request->toArray(), [
            'store_id'     => $this->storeId,
            'store_passwd' => $this->storePassword,
        ]);

        $response = Http::sslcommerz()
            ->post(config('sslcommerz.paths.init'), $payload)
            ->throw()
            ->json();

        $session = PaymentSession::fromApiResponse($response);

        if (! $session->success) {
            throw new PaymentInitiationException(
                $session->failedReason ?? 'Payment initiation failed'
            );
        }

        return $session;
    }

    public function validateOrder(
        string $valId,
        string $tranId,
        float  $amount,
        string $currency = 'BDT',
    ): ValidationResult {
        $response = Http::sslcommerz()
            ->get(config('sslcommerz.paths.validation'), [
                'val_id'       => $valId,
                'store_id'     => $this->storeId,
                'store_passwd' => $this->storePassword,
                'v'            => 1,
                'format'       => 'json',
            ])
            ->throw()
            ->json();

        $result = ValidationResult::fromApiResponse($response);

        if ($result->isValid()) {
            if ($currency === 'BDT') {
                if ($tranId !== $result->tranId || abs($amount - $result->amount) >= 1) {
                    throw new OrderValidationException('Data has been tampered');
                }
            } elseif (
                $tranId !== $result->tranId
                || abs($amount - (float) $result->currencyAmount) >= 1
                || $currency !== $result->currencyType
            ) {
                throw new OrderValidationException('Data has been tampered');
            }
        }

        return $result;
    }

    public function verifyIpnHash(array $postData): bool
    {
        if (! isset($postData['verify_sign'], $postData['verify_key'])) {
            return false;
        }

        $keys = explode(',', $postData['verify_key']);
        $data = array_intersect_key($postData, array_flip($keys));
        $data['store_passwd'] = md5($this->storePassword);
        ksort($data);

        $hashString = rtrim(
            implode('&', array_map(
                fn (string $k, mixed $v) => "$k=$v",
                array_keys($data),
                array_values($data),
            )),
            '&',
        );

        return hash_equals(md5($hashString), $postData['verify_sign']);
    }
}
