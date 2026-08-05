<?php

namespace App\Application\Commerce\Analytics\DTOs;

use Carbon\CarbonImmutable;

final readonly class AnalyticsDateRangeDTO
{
    public function __construct(
        public CarbonImmutable $from,
        public CarbonImmutable $to,
        public string          $label,
    ) {
    }

    public function cachePayload(): array
    {
        return [
            // Moving ranges are intentionally rounded to the minute so
            // repeated Livewire renders can reuse the same snapshot.
            'from' => $this->from->startOfMinute()->toISOString(),
            'to' => $this->to->startOfMinute()->toISOString(),
            'label' => $this->label,
        ];
    }
}
