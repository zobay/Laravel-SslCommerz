<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Contracts;

use Zobay\LaravelSslCommerz\Data\PaymentRequest;
use Zobay\LaravelSslCommerz\Data\PaymentSession;
use Zobay\LaravelSslCommerz\Data\ValidationResult;

interface SslCommerzClientInterface
{
    public function initiatePayment(PaymentRequest $request): PaymentSession;

    public function validateOrder(
        string $valId,
        string $tranId,
        float  $amount,
        string $currency,
    ): ValidationResult;

    public function verifyIpnHash(array $postData): bool;
}
