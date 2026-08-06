<?php

namespace App\Application\Commerce\Analytics\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class StoreAnalyticsDTO
{
    /**
     * @param array<string, AnalyticsMetricDTO> $metrics
     * @param Collection<int, array<string, mixed>> $salesTrend
     * @param Collection<int, array<string, mixed>> $productPerformance
     */
    public function __construct(
        public AnalyticsFilterDTO $filter,
        public array $metrics,
        public Collection $salesTrend,
        public Collection $productPerformance,
        public CarbonImmutable $generatedAt,
    ) {
    }
}
