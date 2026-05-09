<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

use Zobay\LaravelSslCommerz\Enums\ShippingMethod;

final readonly class ShipmentData
{
    public function __construct(
        public ShippingMethod $method               = ShippingMethod::No,
        public int            $numOfItems           = 1,
        public ?string        $name                 = null,
        public ?string        $address1             = null,
        public ?string        $address2             = null,
        public ?string        $area                 = null,
        public ?string        $city                 = null,
        public ?string        $subCity              = null,
        public ?string        $state                = null,
        public ?string        $postcode             = null,
        public ?string        $country              = null,
        public ?float         $weightOfItems        = null,
        public ?string        $logisticPickupId     = null,
        public ?string        $logisticDeliveryType = null,
    ) {}

    public function toArray(): array
    {
        return [
            'shipping_method'        => $this->method->value,
            'num_of_item'            => $this->numOfItems,
            'ship_name'              => $this->name,
            'ship_add1'              => $this->address1,
            'ship_add2'              => $this->address2,
            'ship_area'              => $this->area,
            'ship_city'              => $this->city,
            'ship_sub_city'          => $this->subCity,
            'ship_state'             => $this->state,
            'ship_postcode'          => $this->postcode,
            'ship_country'           => $this->country,
            'weight_of_items'        => $this->weightOfItems,
            'logistic_pickup_id'     => $this->logisticPickupId,
            'logistic_delivery_type' => $this->logisticDeliveryType,
        ];
    }
}
