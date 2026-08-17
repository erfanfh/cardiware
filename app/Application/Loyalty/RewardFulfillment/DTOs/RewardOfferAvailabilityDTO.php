<?php

namespace App\Application\Loyalty\RewardFulfillment\DTOs;

final readonly class RewardOfferAvailabilityDTO
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public bool $canRedeem,
        public ?string $reason = null,
        public array $context = [],
    ) {
    }

    /** @param array<string, mixed> $context */
    public static function allowed(array $context = []): self
    {
        return new self(true, null, $context);
    }

    /** @param array<string, mixed> $context */
    public static function denied(string $reason, array $context = []): self
    {
        return new self(false, $reason, $context);
    }
}
