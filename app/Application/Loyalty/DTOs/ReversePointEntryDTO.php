<?php

namespace App\Application\Loyalty\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

final readonly class ReversePointEntryDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public PointLedgerEntry $entry,
        public int $amount,
        public string $idempotencyKey,
        public ?User $actor = null,
        public ?Model $reference = null,
        public ?string $description = null,
        public array $metadata = [],
        public ?CarbonInterface $occurredAt = null,
    ) {
    }
}
