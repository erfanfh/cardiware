<?php

namespace App\Application\Loyalty\Earning\DTOs;

use App\Application\Loyalty\Earning\Enums\EarningRuleSkipReasonEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Database\Eloquent\Model;

final readonly class EarningRuleEvaluation
{
    /**
     * @param array<string, mixed> $metadata
     */
    private function __construct(
        public ?EarningRule $rule,
        public bool $eligible,
        public int $points,
        public ?string $reason,
        public ?string $idempotencyKey,
        public ?Model $reference,
        public ?string $description,
        public array $metadata,
        public ?EarningRuleSkipReasonEnum $skipReason,
    ) {
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function award(
        EarningRule $rule,
        int $points,
        string $reason,
        string $idempotencyKey,
        ?Model $reference = null,
        ?string $description = null,
        array $metadata = [],
    ): self {
        return new self(
            rule: $rule,
            eligible: true,
            points: $points,
            reason: $reason,
            idempotencyKey: $idempotencyKey,
            reference: $reference,
            description: $description,
            metadata: $metadata,
            skipReason: null,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function skip(
        ?EarningRule $rule,
        EarningRuleSkipReasonEnum $skipReason,
        array $metadata = [],
    ): self {
        return new self(
            rule: $rule,
            eligible: false,
            points: 0,
            reason: null,
            idempotencyKey: null,
            reference: null,
            description: null,
            metadata: $metadata,
            skipReason: $skipReason,
        );
    }


    public static function fromExisting(PointLedgerEntry $entry): self
    {
        $entry->loadMissing(['earningRule', 'reference']);

        return new self(
            rule: $entry->earningRule,
            eligible: true,
            points: (int)$entry->amount,
            reason: $entry->reason,
            idempotencyKey: $entry->idempotency_key,
            reference: $entry->reference,
            description: $entry->description,
            metadata: $entry->metadata ?? [],
            skipReason: null,
        );
    }

    public function shouldAward(): bool
    {
        return $this->eligible && $this->points > 0;
    }
}
