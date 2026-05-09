<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
use Zobay\LaravelSslCommerz\DTOs\IpnData;
use Zobay\LaravelSslCommerz\DTOs\PaymentRequestData;
use Zobay\LaravelSslCommerz\DTOs\PaymentResponseData;
use Zobay\LaravelSslCommerz\DTOs\ValidationRequestData;
use Zobay\LaravelSslCommerz\DTOs\ValidationResponseData;
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

    public function initiatePayment(PaymentRequestData $request): PaymentResponseData
    {
        $payload = array_merge($request->toArray(), [
            'store_id'     => $this->storeId,
            'store_passwd' => $this->storePassword,
        ]);

        $response = Http::sslcommerz()
            ->post(config('sslcommerz.paths.init'), $payload)
            ->throw()
            ->json();

        $session = PaymentResponseData::fromApiResponse($response);

        if (! $session->success) {
            throw new PaymentInitiationException(
                $session->failedReason ?? 'Payment initiation failed'
            );
        }

        return $session;
    }

    public function validateOrder(ValidationRequestData $validationRequest): ValidationResponseData
    {
        $response = Http::sslcommerz()
            ->get(config('sslcommerz.paths.validation'), array_merge(
                [
                    'store_id'     => $this->storeId,
                    'store_passwd' => $this->storePassword,
                    'v'            => config('sslcommerz.validation_version'),
                    'format'       => 'json',
                ],
                $validationRequest->toArray(),
            ))
            ->throw()
            ->json();

        $result = ValidationResponseData::fromApiResponse($response);

        if ($result->isValid()) {
            if ($validationRequest->currency === 'BDT') {
                if ($validationRequest->tranId !== $result->tranId || abs($validationRequest->amount - $result->amount) >= 1) {
                    throw new OrderValidationException('Data has been tampered');
                }
            } elseif (
                $validationRequest->tranId !== $result->tranId
                || abs($validationRequest->amount - (float) $result->currencyAmount) >= 1
                || $validationRequest->currency !== $result->currencyType
            ) {
                throw new OrderValidationException('Data has been tampered');
            }
        }

        return $result;
    }

    public function verifyIpnHash(IpnData $payload): bool
    {
        $keys = explode(',', $payload->verifyKey);
        $data = array_intersect_key($payload->getRaw(), array_flip($keys));
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

        return hash_equals(md5($hashString), $payload->verifySign);
    }
}
