<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Financial\Enums\FinancialActivityDirectionEnum;
use App\Domain\Financial\Enums\FinancialActivitySourceEnum;
use App\Domain\Financial\Enums\FinancialActivityStatusEnum;
use App\Domain\Financial\Enums\FinancialActivityTypeEnum;
use Carbon\CarbonInterface;

final readonly class FinancialActivityDTO
{
    /**
     * @param array<string, mixed> $details
     */
    public function __construct(
        /**
         * Unique UI key, such as payment:15.
         */
        public string                          $key,

        /**
         * Internal model source.
         */
        public FinancialActivitySourceEnum     $source,

        /**
         * Primary key of the original source model.
         */
        public int                             $sourceId,

        /**
         * User-facing financial activity type.
         */
        public FinancialActivityTypeEnum       $type,

        /**
         * Normalized status shared by all financial models.
         */
        public FinancialActivityStatusEnum     $status,

        /**
         * Null means no completed financial movement yet.
         */
        public ?FinancialActivityDirectionEnum $direction,

        public string                          $title,

        public ?string                         $description,

        /**
         * Amount in toman.
         */
        public int                             $amount,

        public CarbonInterface                 $occurredAt,

        public ?string                         $publicToken = null,

        public ?string                         $trackingCode = null,

        public ?string                         $referenceLabel = null,

        public ?string                         $referenceUrl = null,

        /**
         * Only wallet ledger activities normally have this value.
         */
        public ?int                            $balanceAfter = null,

        /**
         * Extra user-facing details.
         */
        public array                           $details = [],
    )
    {
    }

    public function hasFinancialEffect(): bool
    {
        return $this->direction !== null && $this->status->hasCompletedFinancialEffect();
    }

    public function isCredit(): bool
    {
        return $this->hasFinancialEffect() && $this->direction?->isCredit() === true;
    }

    public function isDebit(): bool
    {
        return $this->hasFinancialEffect() && $this->direction?->isDebit() === true;
    }

    public function amountSign(): ?string
    {
        if (!$this->hasFinancialEffect()) {
            return null;
        }

        return $this->direction?->sign();
    }

    public function transactionCode(): ?string
    {
        if (!$this->publicToken) {
            return null;
        }

        return substr($this->publicToken, -10);
    }
}
