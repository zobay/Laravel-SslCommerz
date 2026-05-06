<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz;

use Zobay\LaravelSslCommerz\Contracts\SslCommerzClientInterface;
use Zobay\LaravelSslCommerz\Data\PaymentRequest;
use Zobay\LaravelSslCommerz\Data\PaymentSession;
use Zobay\LaravelSslCommerz\Data\ValidationResult;

class LaravelSslCommerz
{
    private SslCommerzClientInterface $client;

    public function __construct()
    {
        $baseUrl = config('sslcommerz.sandbox')
            ? config('sslcommerz.sandbox_base_url')
            : config('sslcommerz.live_base_url');

        $this->client = new SslCommerzApiClient(
            storeId:       config('sslcommerz.credentials.store_id'),
            storePassword: config('sslcommerz.credentials.store_passwd'),
            baseUrl:       $baseUrl,
        );
    }

    public function initiatePayment(PaymentRequest $request): PaymentSession
    {
        return $this->client->initiatePayment($request);
    }

    public function validateOrder(
        string $valId,
        string $tranId,
        float  $amount,
        string $currency = 'BDT',
    ): ValidationResult {
        return $this->client->validateOrder($valId, $tranId, $amount, $currency);
    }

    public function verifyIpnHash(array $postData): bool
    {
        return $this->client->verifyIpnHash($postData);
    }
}
