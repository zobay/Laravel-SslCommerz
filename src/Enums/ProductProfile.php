<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Enums;

enum ProductProfile: string
{
    case General          = 'general';
    case PhysicalGoods    = 'physical-goods';
    case NonPhysicalGoods = 'non-physical-goods';
    case AirlineTickets   = 'airline-tickets';
    case TravelVertical   = 'travel-vertical';
    case TelecomVertical  = 'telecom-vertical';
}
