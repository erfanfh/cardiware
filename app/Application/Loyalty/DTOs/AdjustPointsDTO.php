<?php

namespace App\Application\Loyalty\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Database\Eloquent\Model;

final readonly class AdjustPointsDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public PointAccount $account,
        public PointEntryDirectionEnum $direction,
        public int $amount,
        public string $reason,
        public string $idempotencyKey,
        public ?User $actor = null,
        public ?Model $reference = null,
        public ?string $description = null,
        public array $metadata = [],
        // true: XP سطح هم به همین اندازه تغییر می‌کند (reason=manual_xp_adjustment)
        public bool $affectsXp = false,
    ) {
    }
}
