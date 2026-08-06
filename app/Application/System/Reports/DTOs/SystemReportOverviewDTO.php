<?php

namespace App\Application\System\Reports\DTOs;

use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\AnalyticsMetricDTO;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class SystemReportOverviewDTO
{
    /**
     * @param array<string, AnalyticsMetricDTO> $metrics
     * @param Collection<int, array<string, mixed>> $trend
     * @param Collection<int, array<string, mixed>> $otpTypes
     * @param Collection<int, array<string, mixed>> $topDownloadedProducts
     * @param array<string, int> $systemSnapshot
     * @param array<string, int> $commentStatuses
     * @param array<string, int> $ticketStatuses
     */
    public function __construct(
        public AnalyticsFilterDTO $filter,
        public array $metrics,
        public Collection $trend,
        public Collection $otpTypes,
        public Collection $topDownloadedProducts,
        public array $systemSnapshot,
        public array $commentStatuses,
        public array $ticketStatuses,
        public CarbonImmutable $generatedAt,
    ) {
    }
}
