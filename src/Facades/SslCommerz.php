<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Facades;

use Illuminate\Support\Facades\Facade;
use Zobay\LaravelSslCommerz\DTOs\CustomerData;
use Zobay\LaravelSslCommerz\DTOs\EmiData;
use Zobay\LaravelSslCommerz\DTOs\PaymentResponseData;
use Zobay\LaravelSslCommerz\DTOs\ProductData;
use Zobay\LaravelSslCommerz\DTOs\ShipmentData;
use Zobay\LaravelSslCommerz\DTOs\ValidationResponseData;

/**
 * @see \Zobay\LaravelSslCommerz\LaravelSslCommerz
 *
 * @method static PaymentResponseData    initiatePayment(string $tranId, float $totalAmount, string $currency, string $successUrl, string $failUrl, string $cancelUrl, CustomerData $customer, ProductData $product, ?ShipmentData $shipment = null, ?string $ipnUrl = null, ?EmiData $emi = null, ?string $multiCardName = null, ?string $allowedBin = null, ?string $valueA = null, ?string $valueB = null, ?string $valueC = null, ?string $valueD = null)
 * @method static ValidationResponseData validateOrder(string $valId, string $tranId, float $amount, string $currency = 'BDT')
 * @method static bool                   verifyIpnHash(array $postData)
 */
class SslCommerz extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Zobay\LaravelSslCommerz\LaravelSslCommerz::class;
    }
}
