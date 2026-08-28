<?php

namespace App\Application\Raffle\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Carbon\CarbonImmutable;

final readonly class TransitionDrawCampaignStatusDTO
{
    public function __construct(
        public DrawCampaign $campaign,
        public DrawCampaignStatusEnum $status,
        public ?User $actor = null,
        public ?CarbonImmutable $occurredAt = null,
        public ?string $reason = null,
    ) {
    }
}
