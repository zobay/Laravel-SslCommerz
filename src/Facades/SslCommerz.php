<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Facades;

use Illuminate\Support\Facades\Facade;
use Zobay\LaravelSslCommerz\DTOs\PaymentRequestData;
use Zobay\LaravelSslCommerz\DTOs\PaymentResponseData;
use Zobay\LaravelSslCommerz\DTOs\ValidationResponseData;

/**
 * @see \Zobay\LaravelSslCommerz\LaravelSslCommerz
 *
 * @method static PaymentResponseData   initiatePayment(PaymentRequestData $request)
 * @method static ValidationResponseData validateOrder(string $valId, string $tranId, float $amount, string $currency = 'BDT')
 * @method static bool             verifyIpnHash(array $postData)
 */
class SslCommerz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Zobay\LaravelSslCommerz\LaravelSslCommerz::class;
    }
}
