<?php

namespace App\Livewire\Admin\Commerce\Analytics;

use App\Application\Commerce\Analytics\Actions\GetProductAnalyticsAction;
use App\Application\Commerce\Analytics\DTOs\AnalyticsFilterDTO;
use App\Application\Commerce\Analytics\DTOs\ProductAnalyticsDTO;
use App\Application\Commerce\Analytics\Enums\AnalyticsComparisonEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsGranularityEnum;
use App\Application\Commerce\Analytics\Enums\AnalyticsRangePresetEnum;
use App\Application\Commerce\Analytics\Support\AnalyticsRangeResolver;
use App\Domain\Catalog\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class ProductAnalyticsPage extends Component
{
    public int $productId;

    public string $preset = AnalyticsRangePresetEnum::LAST_30_DAYS->value;

    public string $comparison = AnalyticsComparisonEnum::PREVIOUS_PERIOD->value;

    public string $granularity = AnalyticsGranularityEnum::AUTO->value;

    public string $customFrom = '';

    public string $customTo = '';

    public string $draftCustomFrom = '';

    public string $draftCustomTo = '';

    protected $queryString = [
        'preset' => [
            'except' => AnalyticsRangePresetEnum::LAST_30_DAYS->value,
        ],
        'comparison' => [
            'except' => AnalyticsComparisonEnum::PREVIOUS_PERIOD->value,
        ],
        'granularity' => [
            'except' => AnalyticsGranularityEnum::AUTO->value,
        ],
        'customFrom' => [
            'except' => '',
        ],
        'customTo' => [
            'except' => '',
        ],
    ];

    public function mount(
        Product $product,
        AnalyticsRangeResolver $rangeResolver,
    ): void {
        Gate::authorize('analytics.view');

        $this->productId = (int)$product->getKey();
        $this->normalizeFilters($rangeResolver);
        $this->syncDraftCustomRange($rangeResolver);
    }

    public function setPreset(
        string $preset,
        AnalyticsRangeResolver $rangeResolver,
    ): void {
        $this->preset = AnalyticsRangePresetEnum::tryFrom($preset)?->value
            ?? AnalyticsRangePresetEnum::LAST_30_DAYS->value;

        if ($this->preset === AnalyticsRangePresetEnum::CUSTOM->value) {
            $this->ensureAppliedCustomRange($rangeResolver);
            $this->draftCustomFrom = $this->customFrom;
            $this->draftCustomTo = $this->customTo;
        }
    }

    public function applyCustomRange(AnalyticsRangeResolver $rangeResolver): void
    {
        $this->resetErrorBag('customRange');

        try {
            $range = $rangeResolver->resolveCustomRange(
                $this->draftCustomFrom,
                $this->draftCustomTo,
            );
        } catch (InvalidArgumentException $exception) {
            $this->addError('customRange', $exception->getMessage());

            return;
        }

        $this->customFrom = $rangeResolver->formatJalaliDate($range->from);
        $this->customTo = $rangeResolver->formatJalaliDate($range->to);
        $this->draftCustomFrom = $this->customFrom;
        $this->draftCustomTo = $this->customTo;
        $this->preset = AnalyticsRangePresetEnum::CUSTOM->value;
    }

    public function refreshReport(
        AnalyticsRangeResolver $rangeResolver,
        GetProductAnalyticsAction $getProductAnalyticsAction,
    ): void {
        $this->normalizeFilters($rangeResolver);

        $getProductAnalyticsAction->forget(
            productId: $this->productId,
            filter: $this->resolveFilter($rangeResolver),
        );
    }

    public function render(
        AnalyticsRangeResolver $rangeResolver,
        GetProductAnalyticsAction $getProductAnalyticsAction,
    ): View {
        Gate::authorize('analytics.view');
        $this->normalizeFilters($rangeResolver);

        $product = Product::query()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->with(['store', 'picture'])
            ->findOrFail($this->productId);

        $filter = $this->resolveFilter($rangeResolver);
        $analytics = $getProductAnalyticsAction->execute(
            productId: $this->productId,
            filter: $filter,
        );

        return view('livewire.admin.commerce.analytics.product-analytics-page', [
            'product' => $product,
            'analytics' => $analytics,
            'presets' => AnalyticsRangePresetEnum::cases(),
            'comparisonOptions' => AnalyticsComparisonEnum::cases(),
            'granularityOptions' => AnalyticsGranularityEnum::cases(),
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
            'chartPayload' => $this->buildChartPayload($analytics),
        ]);
    }

    private function resolveFilter(
        AnalyticsRangeResolver $rangeResolver,
    ): AnalyticsFilterDTO {
        return $rangeResolver->resolveFilter(
            preset: AnalyticsRangePresetEnum::from($this->preset),
            comparison: AnalyticsComparisonEnum::from($this->comparison),
            granularity: AnalyticsGranularityEnum::from($this->granularity),
            customFrom: $this->customFrom,
            customTo: $this->customTo,
        );
    }

    private function normalizeFilters(AnalyticsRangeResolver $rangeResolver): void
    {
        if (!AnalyticsRangePresetEnum::tryFrom($this->preset)) {
            $this->preset = AnalyticsRangePresetEnum::LAST_30_DAYS->value;
        }

        if (!AnalyticsComparisonEnum::tryFrom($this->comparison)) {
            $this->comparison = AnalyticsComparisonEnum::PREVIOUS_PERIOD->value;
        }

        if (!AnalyticsGranularityEnum::tryFrom($this->granularity)) {
            $this->granularity = AnalyticsGranularityEnum::AUTO->value;
        }

        if ($this->preset === AnalyticsRangePresetEnum::CUSTOM->value) {
            $this->ensureAppliedCustomRange($rangeResolver);
        }
    }

    private function ensureAppliedCustomRange(
        AnalyticsRangeResolver $rangeResolver,
    ): void {
        try {
            $rangeResolver->resolveCustomRange($this->customFrom, $this->customTo);
        } catch (InvalidArgumentException) {
            $this->setDefaultCustomRange($rangeResolver);
        }
    }

    private function syncDraftCustomRange(
        AnalyticsRangeResolver $rangeResolver,
    ): void {
        if ($this->customFrom === '' || $this->customTo === '') {
            $range = $rangeResolver->resolveRange(
                AnalyticsRangePresetEnum::LAST_30_DAYS,
            );

            $this->draftCustomFrom = $rangeResolver->formatJalaliDate($range->from);
            $this->draftCustomTo = $rangeResolver->formatJalaliDate($range->to);

            return;
        }

        $this->draftCustomFrom = $this->customFrom;
        $this->draftCustomTo = $this->customTo;
    }

    private function setDefaultCustomRange(
        AnalyticsRangeResolver $rangeResolver,
    ): void {
        $range = $rangeResolver->resolveRange(
            AnalyticsRangePresetEnum::LAST_30_DAYS,
        );

        $this->customFrom = $rangeResolver->formatJalaliDate($range->from);
        $this->customTo = $rangeResolver->formatJalaliDate($range->to);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildChartPayload(ProductAnalyticsDTO $analytics): array
    {
        return [
            'granularity' => $analytics->filter->granularity->value,
            'granularityLabel' => $analytics->filter->granularity->label(),
            'comparisonEnabled' => $analytics->filter->comparisonRange !== null,
            'rows' => $analytics->salesTrend->values()->all(),
        ];
    }

    private function formatRange(CarbonImmutable $from, CarbonImmutable $to): string
    {
        $fromLabel = Jalalian::fromCarbon($from->toMutable())->format('Y/m/d');
        $toLabel = Jalalian::fromCarbon($to->toMutable())->format('Y/m/d');

        if ($fromLabel === $toLabel) {
            return $fromLabel;
        }

        return $fromLabel . ' تا ' . $toLabel;
    }
}
