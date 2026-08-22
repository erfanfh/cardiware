<?php

namespace App\Application\Raffle\Actions\Front;

use App\Application\Raffle\Actions\Draw\VerifyDrawRunAction;
use App\Application\Raffle\Support\PublicDrawWinnerPresenter;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class GetPublicDrawCampaignDetailAction
{
    public function __construct(
        private readonly PublicDrawWinnerPresenter $winnerPresenter,
        private readonly VerifyDrawRunAction $verifyDrawRun,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $slug, ?User $user = null): array
    {
        $campaign = DrawCampaign::query()
            ->where('slug', $slug)
            ->whereIn('status', $this->visibleStatuses())
            ->with([
                'coverMediaFile.primaryVariant',
                'prizes.mediaFile.primaryVariant',
            ])
            ->first();

        if (! $campaign) {
            throw (new ModelNotFoundException)->setModel(DrawCampaign::class, [$slug]);
        }

        $myTickets = collect();
        $myActiveTicketsCount = 0;
        $myTotalTicketsCount = 0;
        $myWinningRecords = collect();

        if ($user) {
            $myTickets = DrawTicket::query()
                ->where('campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->latest('issued_at')
                ->limit(8)
                ->get();

            $myActiveTicketsCount = DrawTicket::query()
                ->where('campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->where('status', DrawTicketStatusEnum::ACTIVE->value)
                ->count();

            $myTotalTicketsCount = DrawTicket::query()
                ->where('campaign_id', $campaign->id)
                ->where('user_id', $user->id)
                ->count();

            $myWinningRecords = DrawWinner::query()
                ->where('campaign_id', $campaign->id)
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
                ->with(['prize', 'ticket'])
                ->orderBy('prize_id')
                ->orderBy('position')
                ->get();
        }

        $publicWinners = $this->publicWinners($campaign);
        $publicDrawRuns = $this->publicDrawRuns($campaign);

        return compact(
            'campaign',
            'myTickets',
            'myActiveTicketsCount',
            'myTotalTicketsCount',
            'myWinningRecords',
            'publicWinners',
            'publicDrawRuns',
        );
    }

    private function publicDrawRuns(DrawCampaign $campaign): Collection
    {
        return DrawRun::query()
            ->where('campaign_id', $campaign->getKey())
            ->whereIn('status', [
                DrawRunStatusEnum::PENDING->value,
                DrawRunStatusEnum::RUNNING->value,
                DrawRunStatusEnum::COMPLETED->value,
            ])
            ->select([
                'id', 'public_token', 'campaign_id', 'type', 'status', 'algorithm',
                'seed_commitment', 'seed_reveal', 'eligible_ticket_count',
                'eligible_set_hash', 'started_at', 'completed_at', 'created_at',
            ])
            ->latest('id')
            ->get()
            ->map(function (DrawRun $run): array {
                $verification = $run->status === DrawRunStatusEnum::COMPLETED
                    ? $this->verifyDrawRun->execute($run)
                    : null;

                return [
                    'run' => $run,
                    'verification' => $verification,
                ];
            });
    }

    private function publicWinners(DrawCampaign $campaign): Collection
    {
        if (
            ! $campaign->show_winners_publicly
            || $campaign->status !== DrawCampaignStatusEnum::COMPLETED
        ) {
            return collect();
        }

        return DrawWinner::query()
            ->where('campaign_id', $campaign->id)
            ->where(function ($query): void {
                $query
                    ->where('kind', DrawWinnerKindEnum::PRIMARY->value)
                    ->orWhereNotNull('promoted_at');
            })
            ->whereIn('status', [
                DrawWinnerStatusEnum::CONFIRMED->value,
                DrawWinnerStatusEnum::DELIVERED->value,
            ])
            ->with(['user', 'prize', 'ticket'])
            ->orderBy('prize_id')
            ->orderByRaw('COALESCE(promoted_for_position, position) ASC')
            ->get()
            ->map(function (DrawWinner $winner): array {
                return [
                    'id' => $winner->id,
                    'name' => $this->winnerPresenter->maskedName($winner->user),
                    'ticket_code' => $this->winnerPresenter->maskedTicketCode($winner->ticket?->code),
                    'prize_title' => $winner->prize?->title,
                    'position' => $winner->effectivePrimaryPosition(),
                    'delivered' => $winner->status === DrawWinnerStatusEnum::DELIVERED,
                ];
            });
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
