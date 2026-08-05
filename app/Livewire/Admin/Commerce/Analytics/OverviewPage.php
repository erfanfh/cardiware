<?php

namespace App\Livewire\Admin\Commerce\Analytics;

use App\Application\Commerce\Analytics\Actions\GetCommerceOverviewAction;
use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;
use App\Application\Commerce\Analytics\Support\AnalyticsRangeResolver;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class OverviewPage extends Component
{
    public string $preset = AnalyticsRangePresetEnum::LAST_30_DAYS->value;

    public string $comparison = AnalyticsComparisonEnum::PREVIOUS_PERIOD->value;

    protected $queryString = [
        'preset' => [
            'except' => AnalyticsRangePresetEnum::LAST_30_DAYS->value,
        ],
        'comparison' => [
            'except' => AnalyticsComparisonEnum::PREVIOUS_PERIOD->value,
        ],
    ];

    public function mount(): void
    {
        Gate::authorize('analytics.view');

        $this->normalizeFilters();
    }

    public function setPreset(string $preset): void
    {
        $this->preset = AnalyticsRangePresetEnum::tryFrom($preset)?->value
            ?? AnalyticsRangePresetEnum::LAST_30_DAYS->value;
    }

    public function setComparison(string $comparison): void
    {
        $this->comparison = AnalyticsComparisonEnum::tryFrom($comparison)?->value
            ?? AnalyticsComparisonEnum::PREVIOUS_PERIOD->value;
    }

    public function refreshReport(
        AnalyticsRangeResolver    $rangeResolver,
        GetCommerceOverviewAction $getCommerceOverviewAction,
    ): void
    {
        $this->normalizeFilters();

        $filter = $rangeResolver->resolveFilter(
            preset: AnalyticsRangePresetEnum::from($this->preset),
            comparison: AnalyticsComparisonEnum::from($this->comparison),
        );

        $getCommerceOverviewAction->forget($filter);
    }

    public function render(
        AnalyticsRangeResolver    $rangeResolver,
        GetCommerceOverviewAction $getCommerceOverviewAction,
    ): View
    {
        $this->normalizeFilters();

        $filter = $rangeResolver->resolveFilter(
            preset: AnalyticsRangePresetEnum::from($this->preset),
            comparison: AnalyticsComparisonEnum::from($this->comparison),
        );

        $overview = $getCommerceOverviewAction->execute($filter);

        return view('livewire.admin.commerce.analytics.overview-page', [
            'overview' => $overview,
            'presets' => AnalyticsRangePresetEnum::cases(),
            'comparisonOptions' => AnalyticsComparisonEnum::cases(),
            'rangeLabel' => $this->formatRange(
                $filter->range->from,
                $filter->range->to,
            ),
            'comparisonRangeLabel' => $filter->comparisonRange
                ? $this->formatRange(
                    $filter->comparisonRange->from,
                    $filter->comparisonRange->to,
                )
                : null,
        ]);
    }

    private function normalizeFilters(): void
    {
        if (!AnalyticsRangePresetEnum::tryFrom($this->preset)) {
            $this->preset = AnalyticsRangePresetEnum::LAST_30_DAYS->value;
        }

        if (!AnalyticsComparisonEnum::tryFrom($this->comparison)) {
            $this->comparison = AnalyticsComparisonEnum::PREVIOUS_PERIOD->value;
        }
    }

    private function formatRange(
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): string
    {
        $fromLabel = Jalalian::fromCarbon($from->toMutable())->format('Y/m/d');

        $toLabel = Jalalian::fromCarbon($to->toMutable())->format('Y/m/d');

        if ($fromLabel === $toLabel) {
            return $fromLabel;
        }

        return $fromLabel . ' تا ' . $toLabel;
    }
}
