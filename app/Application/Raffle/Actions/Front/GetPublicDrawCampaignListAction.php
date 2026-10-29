<?php

namespace App\Application\Raffle\Actions\Front;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawPrize;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GetPublicDrawCampaignListAction
{
    /**
     * @return array{campaigns: LengthAwarePaginator, stats: array<string, int>, filter: string}
     */
    public function execute(string $filter = 'all'): array
    {
        $page = max(1, Paginator::resolveCurrentPage());
        $data = app(PublicReadCache::class)->remember(
            'raffle-list', [$filter, $page], [DrawCampaign::class, DrawPrize::class, MediaFile::class],
            fn () => $this->queryData($filter, $page),
            seconds: 60,
        );
        $data['campaigns'] = (clone $data['campaigns'])->withPath(request()->url())->withQueryString();

        return $data;
    }

    private function queryData(string $filter, int $page): array
    {
        $visibleStatuses = $this->visibleStatuses();

        $query = DrawCampaign::query()
            ->whereIn('status', $visibleStatuses)
            ->with([
                'coverMediaFile.primaryVariant',
                'prizes.mediaFile.primaryVariant',
            ])
            ->withSum('prizes as prize_units_count', 'quantity');

        match ($filter) {
            'open' => $query->where('status', DrawCampaignStatusEnum::OPEN->value),
            'upcoming' => $query->where('status', DrawCampaignStatusEnum::SCHEDULED->value),
            'finished' => $query->whereIn('status', [
                DrawCampaignStatusEnum::CLOSED->value,
                DrawCampaignStatusEnum::DRAWING->value,
                DrawCampaignStatusEnum::COMPLETED->value,
            ]),
            default => null,
        };

        $campaigns = $query
            ->orderByRaw("CASE status
                WHEN 'open' THEN 1
                WHEN 'scheduled' THEN 2
                WHEN 'drawing' THEN 3
                WHEN 'closed' THEN 4
                WHEN 'completed' THEN 5
                ELSE 6
            END")
            ->orderByRaw('CASE WHEN status = ? THEN closes_at END ASC', [DrawCampaignStatusEnum::OPEN->value])
            ->orderByRaw('CASE WHEN status = ? THEN starts_at END ASC', [DrawCampaignStatusEnum::SCHEDULED->value])
            ->latest('id')
            ->paginate(9, ['*'], 'page', $page);

        $baseStatsQuery = DrawCampaign::query()->whereIn('status', $visibleStatuses);

        $stats = [
            'open_campaigns' => (clone $baseStatsQuery)
                ->where('status', DrawCampaignStatusEnum::OPEN->value)
                ->count(),
            'upcoming_campaigns' => (clone $baseStatsQuery)
                ->where('status', DrawCampaignStatusEnum::SCHEDULED->value)
                ->count(),
            'completed_campaigns' => (clone $baseStatsQuery)
                ->where('status', DrawCampaignStatusEnum::COMPLETED->value)
                ->count(),
            'active_tickets' => (int) (clone $baseStatsQuery)
                ->where('status', DrawCampaignStatusEnum::OPEN->value)
                ->sum('active_tickets_count'),
        ];

        return compact('campaigns', 'stats', 'filter');
    }

    /** @return list<string> */
    private function visibleStatuses(): array
    {
        return [
            DrawCampaignStatusEnum::SCHEDULED->value,
            DrawCampaignStatusEnum::OPEN->value,
            DrawCampaignStatusEnum::CLOSED->value,
            DrawCampaignStatusEnum::DRAWING->value,
            DrawCampaignStatusEnum::COMPLETED->value,
        ];
    }
}
