<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Illuminate\Support\Facades\Http;
use Zobay\LaravelSslCommerz\DTOs\CustomerData;
use Zobay\LaravelSslCommerz\DTOs\EmiData;
use Zobay\LaravelSslCommerz\DTOs\PaymentResponseData;
use Zobay\LaravelSslCommerz\DTOs\ProductData;
use Zobay\LaravelSslCommerz\DTOs\ShipmentData;
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

    public function initiatePayment(
        string        $tranId,
        float         $totalAmount,
        string        $currency,
        string        $successUrl,
        string        $failUrl,
        string        $cancelUrl,
        CustomerData  $customer,
        ProductData   $product,
        ?ShipmentData $shipment      = null,
        ?string       $ipnUrl        = null,
        ?EmiData      $emi           = null,
        ?string       $multiCardName = null,
        ?string       $allowedBin    = null,
        ?string       $valueA        = null,
        ?string       $valueB        = null,
        ?string       $valueC        = null,
        ?string       $valueD        = null,
    ): PaymentResponseData {
        $payload = array_filter(array_merge(
            [
                'tran_id'         => $tranId,
                'total_amount'    => $totalAmount,
                'currency'        => $currency,
                'success_url'     => $successUrl,
                'fail_url'        => $failUrl,
                'cancel_url'      => $cancelUrl,
                'ipn_url'         => $ipnUrl,
                'multi_card_name' => $multiCardName,
                'allowed_bin'     => $allowedBin,
                'value_a'         => $valueA,
                'value_b'         => $valueB,
                'value_c'         => $valueC,
                'value_d'         => $valueD,
                'store_id'        => $this->storeId,
                'store_passwd'    => $this->storePassword,
            ],
            $customer->toArray(),
            $product->toArray(),
            ($shipment ?? new ShipmentData())->toArray(),
            ($emi ?? new EmiData())->toArray(),
        ), fn ($v) => $v !== null);

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

    public function validateOrder(
        string $valId,
        string $tranId,
        float  $amount,
        string $currency = 'BDT',
    ): ValidationResponseData {
        $response = Http::sslcommerz()
            ->get(config('sslcommerz.paths.validation'), [
                'store_id'     => $this->storeId,
                'store_passwd' => $this->storePassword,
                'val_id'       => $valId,
                'v'            => config('sslcommerz.validation_version'),
                'format'       => 'json',
            ])
            ->throw()
            ->json();

        $result = ValidationResponseData::fromApiResponse($response);

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
