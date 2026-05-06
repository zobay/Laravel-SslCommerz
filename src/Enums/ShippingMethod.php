<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Enums;

enum ShippingMethod: string
{
    case Yes                = 'YES';
    case No                 = 'NO';
    case Courier            = 'Courier';
    case SslCommerzLogistic = 'SSLCOMMERZ_LOGISTIC';
}
