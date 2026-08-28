<?php

namespace App\Application\Loyalty\Earning\DTOs;

use App\Domain\Loyalty\Models\PointLedgerEntry;

final readonly class EarningRuleProcessResult
{
    public function __construct(
        public EarningRuleEvaluation $evaluation,
        public ?PointLedgerEntry $entry,
        public bool $newlyAwarded = false,
    ) {}

    public function wasAwarded(): bool
    {
        return $this->entry !== null;
    }

    public function wasNewlyAwarded(): bool
    {
        return $this->entry !== null && $this->newlyAwarded;
    }
}
