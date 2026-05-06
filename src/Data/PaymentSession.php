<?php declare(strict_types=1);

namespace Zobay\LaravelSslCommerz\Data;

final readonly class PaymentSession
{
    public function __construct(
        public bool    $success,
        public ?string $gatewayPageUrl,
        public ?string $storeLogo,
        public ?string $sessionKey,
        public ?array  $gateways,
        public ?string $failedReason,
    ) {}

    public static function fromApiResponse(array $data): self
    {
        $success = ! empty($data['GatewayPageURL']);

        return new self(
            success:        $success,
            gatewayPageUrl: $data['GatewayPageURL'] ?? null,
            storeLogo:      $data['storeLogo'] ?? null,
            sessionKey:     $data['sessionkey'] ?? null,
            gateways:       $data['gw'] ?? null,
            failedReason:   ! empty($data['failedreason']) ? $data['failedreason'] : null,
        );
    }
}
