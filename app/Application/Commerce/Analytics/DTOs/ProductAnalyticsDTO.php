<?php

namespace App\Application\Commerce\Analytics\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class ProductAnalyticsDTO
{
    /**
     * @param array<string, AnalyticsMetricDTO> $metrics
     * @param Collection<int, array<string, mixed>> $salesTrend
     * @param Collection<int, array<string, mixed>> $itemPerformance
     */
    public function __construct(
        public AnalyticsFilterDTO $filter,
        public array $metrics,
        public Collection $salesTrend,
        public Collection $itemPerformance,
        public CarbonImmutable $generatedAt,
    ) {
    }
}
