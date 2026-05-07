<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Facades;

use Illuminate\Support\Facades\Facade;
use Zobay\LaravelSslCommerz\Data\PaymentRequest;
use Zobay\LaravelSslCommerz\Data\PaymentSession;
use Zobay\LaravelSslCommerz\Data\ValidationResult;

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
