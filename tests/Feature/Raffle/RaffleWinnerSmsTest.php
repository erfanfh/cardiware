<?php

namespace Tests\Feature\Raffle;

use App\Application\Raffle\Jobs\SendRaffleWinnerSmsJob;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawRunTypeEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawWinnersSelectedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawPrize;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RaffleWinnerSmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_primary_winner_notification_is_dispatched_but_reserve_is_not(): void
    {
        Queue::fake();
        $campaign = DrawCampaign::query()->create([
            'title' => 'SMS campaign',
            'slug' => 'sms-campaign',
            'status' => DrawCampaignStatusEnum::COMPLETED,
            'reserve_winners_per_prize' => 1,
        ]);
        $user = User::factory()->create();
        $reserveUser = User::factory()->create();
        $prize = DrawPrize::query()->create([
            'campaign_id' => $campaign->id,
            'title' => 'SMS prize',
            'quantity' => 1,
            'reserve_winners_count' => 1,
            'sort_order' => 0,
        ]);
        $run = DrawRun::query()->create([
            'campaign_id' => $campaign->id,
            'type' => DrawRunTypeEnum::INITIAL,
            'status' => DrawRunStatusEnum::COMPLETED,
            'algorithm' => 'test-v1',
        ]);
        $primaryTicket = $this->ticket($campaign, $user, 'SMS-PRIMARY');
        $reserveTicket = $this->ticket($campaign, $reserveUser, 'SMS-RESERVE');

        $primary = DrawWinner::query()->create([
            'campaign_id' => $campaign->id,
            'draw_run_id' => $run->id,
            'prize_id' => $prize->id,
            'ticket_id' => $primaryTicket->id,
            'user_id' => $user->id,
            'kind' => DrawWinnerKindEnum::PRIMARY,
            'status' => DrawWinnerStatusEnum::PENDING,
            'position' => 1,
            'selected_at' => now(),
        ]);

        $reserve = DrawWinner::query()->create([
            'campaign_id' => $campaign->id,
            'draw_run_id' => $run->id,
            'prize_id' => $prize->id,
            'ticket_id' => $reserveTicket->id,
            'user_id' => $reserveUser->id,
            'kind' => DrawWinnerKindEnum::RESERVE,
            'status' => DrawWinnerStatusEnum::PENDING,
            'position' => 1,
            'selected_at' => now(),
        ]);

        event(new DrawWinnersSelectedEvent(
            campaignId: $campaign->id,
            drawRunId: $run->id,
            winnerIds: [$primary->id, $reserve->id],
            occurredAt: now()->toImmutable(),
        ));

        Queue::assertPushed(SendRaffleWinnerSmsJob::class, fn ($job) => $job->winnerId === $primary->id);
        Queue::assertNotPushed(SendRaffleWinnerSmsJob::class, fn ($job) => $job->winnerId === $reserve->id);
    }

    private function ticket(DrawCampaign $campaign, User $user, string $code): DrawTicket
    {
        $grant = TicketGrant::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'quantity' => 1,
            'idempotency_key' => 'sms:'.$user->id,
            'idempotency_fingerprint' => hash('sha256', 'sms:'.$user->id),
            'issued_at' => now(),
        ]);

        return DrawTicket::query()->create([
            'campaign_id' => $campaign->id,
            'ticket_grant_id' => $grant->id,
            'user_id' => $user->id,
            'code' => $code,
            'status' => DrawTicketStatusEnum::ACTIVE,
            'issued_at' => now(),
        ]);
    }
}
