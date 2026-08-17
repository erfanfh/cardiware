<?php

namespace App\Application\Raffle\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Models\DrawCampaign;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

final readonly class IssueDrawTicketsDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public DrawCampaign $campaign,
        public User $user,
        public int $quantity,
        public string $idempotencyKey,
        public ?Model $source = null,
        public ?User $issuedBy = null,
        public ?CarbonImmutable $occurredAt = null,
        public array $metadata = [],
    ) {
    }
}
