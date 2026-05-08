<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Facades;

use Illuminate\Support\Facades\Facade;
use Zobay\LaravelSslCommerz\DTOs\PaymentRequest;
use Zobay\LaravelSslCommerz\DTOs\PaymentSession;
use Zobay\LaravelSslCommerz\DTOs\ValidationResult;

/**
 * @see \Zobay\LaravelSslCommerz\LaravelSslCommerz
 *
 * @method static PaymentSession   initiatePayment(PaymentRequest $request)
 * @method static ValidationResult validateOrder(string $valId, string $tranId, float $amount, string $currency = 'BDT')
 * @method static bool             verifyIpnHash(array $postData)
 */
class SslCommerz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Zobay\LaravelSslCommerz\LaravelSslCommerz::class;
    }
}
