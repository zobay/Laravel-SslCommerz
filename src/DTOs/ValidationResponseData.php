<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

use Zobay\LaravelSslCommerz\Enums\PaymentStatus;

final readonly class ValidationResponseData
{
    public function __construct(
        public PaymentStatus $status,
        public string        $tranId,
        public string        $valId,
        public float         $amount,
        public float         $storeAmount,
        public string        $currency,
        public string        $bankTranId,
        public string        $cardType,
        public string        $cardBrand,
        public ?string       $cardNo                = null,
        public ?string       $cardIssuer            = null,
        public ?string       $cardIssuerCountry     = null,
        public ?string       $cardIssuerCountryCode = null,
        public ?string       $currencyType          = null,
        public ?float        $currencyAmount        = null,
        public int           $riskLevel             = 0,
        public string        $riskTitle             = 'Safe',
    ) {}

    public static function fromApiResponse(array $data): self
    {
        return new self(
            status:                PaymentStatus::from($data['status']),
            tranId:                $data['tran_id'],
            valId:                 $data['val_id'],
            amount:                (float) $data['amount'],
            storeAmount:           (float) $data['store_amount'],
            currency:              $data['currency'],
            bankTranId:            $data['bank_tran_id'],
            cardType:              $data['card_type'],
            cardBrand:             $data['card_brand'],
            cardNo:                $data['card_no'] ?? null,
            cardIssuer:            $data['card_issuer'] ?? null,
            cardIssuerCountry:     $data['card_issuer_country'] ?? null,
            cardIssuerCountryCode: $data['card_issuer_country_code'] ?? null,
            currencyType:          $data['currency_type'] ?? null,
            currencyAmount:        isset($data['currency_amount']) ? (float) $data['currency_amount'] : null,
            riskLevel:             (int) ($data['risk_level'] ?? 0),
            riskTitle:             $data['risk_title'] ?? 'Safe',
        );
    }

    public function isValid(): bool
    {
        return $this->status === PaymentStatus::Valid
            || $this->status === PaymentStatus::Validated;
    }
}
