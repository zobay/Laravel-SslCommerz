<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Enums;

enum PaymentStatus: string
{
    case Valid              = 'VALID';
    case Validated          = 'VALIDATED';
    case Failed             = 'FAILED';
    case Cancelled          = 'CANCELLED';
    case Unattempted        = 'UNATTEMPTED';
    case Expired            = 'EXPIRED';
    case InvalidTransaction = 'INVALID_TRANSACTION';
}
