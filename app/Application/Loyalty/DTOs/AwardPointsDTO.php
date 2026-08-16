<?php

namespace App\Application\Loyalty\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointAccount;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class AwardPointsDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public PointAccount $account,
        public int $amount,
        public string $reason,
        public string $idempotencyKey,
        public ?EarningRule $earningRule = null,
        public ?User $actor = null,
        public ?Model $reference = null,
        public ?string $description = null,
        public array $metadata = [],
        public ?CarbonInterface $occurredAt = null,
    ) {
    }
}
