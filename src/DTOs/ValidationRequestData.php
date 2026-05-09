<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

final readonly class ValidationRequestData
{
    public function __construct(
        public string $valId,
        public string $tranId,
        public float  $amount,
        public string $currency = 'BDT',
    ) {}

    public function toArray(): array
    {
        return [
            'val_id'   => $this->valId,
            'tran_id'  => $this->tranId,
            'amount'   => $this->amount,
            'currency' => $this->currency,
        ];
    }
}
