<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

use Zobay\LaravelSslCommerz\Enums\ProductProfile;

final readonly class ProductInfo
{
    public function __construct(
        public string         $name,
        public string         $category,
        public ProductProfile $profile            = ProductProfile::General,
        public ?float         $amount             = null,
        public ?float         $vat                = null,
        public ?float         $discountAmount     = null,
        public ?float         $convenienceFee     = null,
        public ?array         $cart               = null,
        public ?string        $hoursTillDeparture = null,
        public ?string        $flightType         = null,
        public ?string        $pnr                = null,
        public ?string        $journeyFromTo      = null,
        public ?string        $thirdPartyBooking  = null,
        public ?string        $hotelName          = null,
        public ?string        $lengthOfStay       = null,
        public ?string        $checkInTime        = null,
        public ?string        $hotelCity          = null,
        public ?string        $productType        = null,
        public ?string        $topupNumber        = null,
        public ?string        $countryTopup       = null,
    ) {}

    public function toArray(): array
    {
        return [
            'product_name'         => $this->name,
            'product_category'     => $this->category,
            'product_profile'      => $this->profile->value,
            'product_amount'       => $this->amount,
            'vat'                  => $this->vat,
            'discount_amount'      => $this->discountAmount,
            'convenience_fee'      => $this->convenienceFee,
            'cart'                 => $this->cart !== null ? json_encode($this->cart) : null,
            'hours_till_departure' => $this->hoursTillDeparture,
            'flight_type'          => $this->flightType,
            'pnr'                  => $this->pnr,
            'journey_from_to'      => $this->journeyFromTo,
            'third_party_booking'  => $this->thirdPartyBooking,
            'hotel_name'           => $this->hotelName,
            'length_of_stay'       => $this->lengthOfStay,
            'check_in_time'        => $this->checkInTime,
            'hotel_city'           => $this->hotelCity,
            'product_type'         => $this->productType,
            'topup_number'         => $this->topupNumber,
            'country_topup'        => $this->countryTopup,
        ];
    }
}
