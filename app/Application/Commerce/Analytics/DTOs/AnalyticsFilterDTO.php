<?php

namespace App\Application\Commerce\Analytics\DTOs;

use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;

final readonly class AnalyticsFilterDTO
{
    public function __construct(
        public AnalyticsRangePresetEnum $preset,
        public AnalyticsDateRangeDTO    $range,
        public AnalyticsComparisonEnum  $comparison,
        public ?AnalyticsDateRangeDTO   $comparisonRange,
    ) {
    }

    public function cacheKey(): string
    {
        return hash('sha256', serialize([
            'preset' => $this->preset->value,
            'range' => $this->range->cachePayload(),
            'comparison' => $this->comparison->value,
            'comparison_range' => $this->comparisonRange?->cachePayload(),
        ]));
    }
}
