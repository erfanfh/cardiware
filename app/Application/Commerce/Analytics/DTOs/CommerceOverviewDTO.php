<?php

namespace App\Application\Commerce\Analytics\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class CommerceOverviewDTO
{
    /**
     * @param array<string, AnalyticsMetricDTO> $metrics
     * @param Collection<int, array<string, mixed>> $salesTrend
     * @param Collection<int, array<string, mixed>> $gatewayPerformance
     * @param Collection<int, array<string, mixed>> $topProducts
     */
    public function __construct(
        public AnalyticsFilterDTO $filter,
        public array              $metrics,
        public Collection         $salesTrend,
        public Collection         $gatewayPerformance,
        public Collection         $topProducts,
        public CarbonImmutable    $generatedAt,
    ) {
    }
}
