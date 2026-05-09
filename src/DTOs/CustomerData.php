<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

final readonly class CustomerData
{
    public function __construct(
        public string  $name,
        public string  $email,
        public string  $phone,
        public ?string $address1 = null,
        public ?string $address2 = null,
        public ?string $city     = null,
        public ?string $state    = null,
        public ?string $postcode = null,
        public ?string $country  = null,
        public ?string $fax      = null,
    ) {}

    public function toArray(): array
    {
        return [
            'cus_name'     => $this->name,
            'cus_email'    => $this->email,
            'cus_phone'    => $this->phone,
            'cus_add1'     => $this->address1,
            'cus_add2'     => $this->address2,
            'cus_city'     => $this->city,
            'cus_state'    => $this->state,
            'cus_postcode' => $this->postcode,
            'cus_country'  => $this->country,
            'cus_fax'      => $this->fax,
        ];
    }
}
