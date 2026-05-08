<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\DTOs;

final readonly class EmiOptions
{
    public function __construct(
        public bool $enabled       = false,
        public ?int $maxInstalment = null,
        public ?int $selectedInst  = null,
        public bool $allowOnly     = false,
    ) {}

    public function toArray(): array
    {
        return [
            'emi_option'          => $this->enabled ? 1 : 0,
            'emi_max_inst_option' => $this->maxInstalment,
            'emi_selected_inst'   => $this->selectedInst,
            'emi_allow_only'      => $this->allowOnly ? 1 : 0,
        ];
    }
}
