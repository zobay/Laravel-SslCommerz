<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
use Zobay\LaravelSslCommerz\Contracts\SslCommerzClientInterface;
use Zobay\LaravelSslCommerz\Data\PaymentRequest;
use Zobay\LaravelSslCommerz\Data\PaymentSession;
use Zobay\LaravelSslCommerz\Data\ValidationResult;

final class SslCommerzApiClient implements SslCommerzClientInterface
{
    private const INIT_PATH       = '/gwprocess/v4/api.php';
    private const VALIDATION_PATH = '/validator/api/validationserverAPI.php';

    public function __construct(
        private readonly string $storeId,
        private readonly string $storePassword,
        private readonly string $baseUrl,
    ) {}

    public function initiatePayment(PaymentRequest $request): PaymentSession
    {
        $payload = array_merge($request->toArray(), [
            'store_id'     => $this->storeId,
            'store_passwd' => $this->storePassword,
        ]);

        $response = Http::asForm()
            ->post($this->baseUrl . self::INIT_PATH, $payload)
            ->throw()
            ->json();

        return PaymentSession::fromApiResponse($response);
    }

    public function validateOrder(
        string $valId,
        string $tranId,
        float  $amount,
        string $currency,
    ): ValidationResult {
        $response = Http::get($this->baseUrl . self::VALIDATION_PATH, [
            'val_id'       => $valId,
            'store_id'     => $this->storeId,
            'store_passwd' => $this->storePassword,
            'v'            => 1,
            'format'       => 'json',
        ])->throw()->json();

        $result = ValidationResult::fromApiResponse($response);

        if ($result->isValid()) {
            if ($currency === 'BDT') {
                if ($tranId !== $result->tranId || abs($amount - $result->amount) >= 1) {
                    throw new \RuntimeException('Data has been tampered');
                }
            } elseif (
                $tranId !== $result->tranId
                || abs($amount - (float) $result->currencyAmount) >= 1
                || $currency !== $result->currencyType
            ) {
                throw new \RuntimeException('Data has been tampered');
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
