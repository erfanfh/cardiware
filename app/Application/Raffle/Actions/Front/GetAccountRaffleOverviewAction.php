<?php

namespace App\Application\Raffle\Actions\Front;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;

class GetAccountRaffleOverviewAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, ?string $campaignToken = null): array
    {
        $campaigns = DrawCampaign::query()
            ->whereHas('tickets', fn($query) => $query->where('user_id', $user->id))
            ->with([
                'coverMediaFile.primaryVariant',
                'prizes.mediaFile.primaryVariant',
            ])
            ->withCount([
                'tickets as my_total_tickets_count' => fn($query) => $query->where('user_id', $user->id),
                'tickets as my_active_tickets_count' => fn($query) => $query
                    ->where('user_id', $user->id)
                    ->where('status', DrawTicketStatusEnum::ACTIVE->value),
            ])
            ->withMax([
                'tickets as my_last_ticket_at' => fn($query) => $query->where('user_id', $user->id),
            ], 'issued_at')
            ->orderByDesc('my_last_ticket_at')
            ->paginate(6, ['*'], 'raffles_page')
            ->withQueryString()
            ->fragment('my-raffles');

        $selectedCampaign = null;

        if ($campaignToken) {
            $selectedCampaign = DrawCampaign::query()
                ->where('public_token', $campaignToken)
                ->whereHas('tickets', fn($query) => $query->where('user_id', $user->id))
                ->first();
        }

        $tickets = DrawTicket::query()
            ->where('user_id', $user->id)
            ->when($selectedCampaign, fn($query) => $query->where('campaign_id', $selectedCampaign->id))
            ->with(['campaign.coverMediaFile.primaryVariant', 'winnerRecords.prize'])
            ->latest('issued_at')
            ->paginate(20, ['*'], 'tickets_page')
            ->withQueryString()
            ->fragment('my-tickets');

        $filterCampaigns = DrawCampaign::query()
            ->whereHas('tickets', fn($query) => $query->where('user_id', $user->id))
            ->orderByDesc('id')
            ->get(['id', 'public_token', 'title', 'status']);

        $winningRecords = DrawWinner::query()
            ->where('user_id', $user->id)
            ->where(function ($query): void {
                $query
                    ->where('kind', DrawWinnerKindEnum::PRIMARY->value)
                    ->orWhereNotNull('promoted_at');
            })
            ->whereIn('status', [
                DrawWinnerStatusEnum::PENDING->value,
                DrawWinnerStatusEnum::CONFIRMED->value,
                DrawWinnerStatusEnum::DELIVERED->value,
            ])
            ->with(['campaign.coverMediaFile.primaryVariant', 'prize', 'ticket'])
            ->latest('selected_at')
            ->get();

        $stats = [
            'campaigns_count' => DrawCampaign::query()
                ->whereHas('tickets', fn($query) => $query->where('user_id', $user->id))
                ->count(),
            'active_tickets_count' => DrawTicket::query()
                ->where('user_id', $user->id)
                ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                ->count(),
            'total_tickets_count' => DrawTicket::query()
                ->where('user_id', $user->id)
                ->count(),
            'winning_positions_count' => DrawWinner::query()
                ->where('user_id', $user->id)
                ->where(function ($query): void {
                    $query
                        ->where('kind', DrawWinnerKindEnum::PRIMARY->value)
                        ->orWhereNotNull('promoted_at');
                })
                ->whereIn('status', [
                    DrawWinnerStatusEnum::PENDING->value,
                    DrawWinnerStatusEnum::CONFIRMED->value,
                    DrawWinnerStatusEnum::DELIVERED->value,
                ])
                ->count(),
        ];

        return compact('campaigns', 'tickets', 'stats', 'selectedCampaign', 'filterCampaigns', 'winningRecords');
    }
}
