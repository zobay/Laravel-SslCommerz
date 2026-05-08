<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

final readonly class PaymentRequest
{
    public function __construct(
        public string       $tranId,
        public float        $totalAmount,
        public string       $currency,
        public string       $successUrl,
        public string       $failUrl,
        public string       $cancelUrl,
        public CustomerInfo $customer,
        public ProductInfo  $product,
        public ?ShipmentInfo $shipment      = null,
        public ?string      $ipnUrl        = null,
        public ?EmiOptions  $emi           = null,
        public ?string      $multiCardName = null,
        public ?string      $allowedBin    = null,
        public ?string      $valueA        = null,
        public ?string      $valueB        = null,
        public ?string      $valueC        = null,
        public ?string      $valueD        = null,
    ) {}

    public function toArray(): array
    {
        $base = [
            'total_amount'    => $this->totalAmount,
            'currency'        => $this->currency,
            'tran_id'         => $this->tranId,
            'success_url'     => $this->successUrl,
            'fail_url'        => $this->failUrl,
            'cancel_url'      => $this->cancelUrl,
            'ipn_url'         => $this->ipnUrl,
            'multi_card_name' => $this->multiCardName,
            'allowed_bin'     => $this->allowedBin,
            'value_a'         => $this->valueA,
            'value_b'         => $this->valueB,
            'value_c'         => $this->valueC,
            'value_d'         => $this->valueD,
        ];

        $merged = array_merge(
            $base,
            $this->customer->toArray(),
            $this->product->toArray(),
            ($this->shipment ?? new ShipmentInfo())->toArray(),
            ($this->emi ?? new EmiOptions())->toArray(),
        );

        return array_filter($merged, fn ($v) => $v !== null);
    }
}
