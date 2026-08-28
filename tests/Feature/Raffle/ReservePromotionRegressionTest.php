<?php

namespace Tests\Feature\Raffle;

use App\Application\Raffle\Actions\Winner\PromoteReserveWinnerAction;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Enums\DrawRunStatusEnum;
use App\Domain\Raffle\Enums\DrawRunTypeEnum;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawPrize;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservePromotionRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_can_be_promoted_and_receive_a_new_confirmation_deadline(): void
    {
        $failedUser = User::factory()->create();
        $reserveUser = User::factory()->create();

        $campaign = DrawCampaign::query()->create([
            'title' => 'Promotion test',
            'slug' => 'promotion-test',
            'status' => DrawCampaignStatusEnum::COMPLETED,
            'allow_multiple_wins_per_user' => false,
            'reserve_winners_per_prize' => 1,
            'winner_confirmation_window_hours' => 72,
        ]);

        $prize = DrawPrize::query()->create([
            'campaign_id' => $campaign->getKey(),
            'title' => 'Prize',
            'quantity' => 1,
            'reserve_winners_count' => 1,
            'sort_order' => 0,
        ]);

        $run = DrawRun::query()->create([
            'campaign_id' => $campaign->getKey(),
            'type' => DrawRunTypeEnum::INITIAL,
            'status' => DrawRunStatusEnum::COMPLETED,
            'algorithm' => 'test-v1',
        ]);

        [$failedTicket, $reserveTicket] = [
            $this->ticket($campaign, $failedUser, 'FAILED-TICKET'),
            $this->ticket($campaign, $reserveUser, 'RESERVE-TICKET'),
        ];

        $failed = DrawWinner::query()->create([
            'campaign_id' => $campaign->getKey(),
            'draw_run_id' => $run->getKey(),
            'prize_id' => $prize->getKey(),
            'ticket_id' => $failedTicket->getKey(),
            'user_id' => $failedUser->getKey(),
            'kind' => DrawWinnerKindEnum::PRIMARY,
            'position' => 1,
            'status' => DrawWinnerStatusEnum::REJECTED,
            'selected_at' => now()->subHour(),
            'confirmation_deadline_at' => now()->subMinutes(30),
            'rejected_at' => now(),
            'rejection_reason' => 'test',
        ]);

        DrawWinner::query()->create([
            'campaign_id' => $campaign->getKey(),
            'draw_run_id' => $run->getKey(),
            'prize_id' => $prize->getKey(),
            'ticket_id' => $reserveTicket->getKey(),
            'user_id' => $reserveUser->getKey(),
            'kind' => DrawWinnerKindEnum::RESERVE,
            'position' => 1,
            'status' => DrawWinnerStatusEnum::PENDING,
            'selected_at' => now()->subHour(),
        ]);

        $promoted = app(PromoteReserveWinnerAction::class)->execute($failed);

        $this->assertNotNull($promoted);
        $this->assertNotNull($promoted->promoted_at);
        $this->assertNotNull($promoted->confirmation_deadline_at);
        $this->assertSame(1, $promoted->promoted_for_position);
        $this->assertSame($failed->getKey(), (int) $promoted->replaces_winner_id);
    }

    private function ticket(DrawCampaign $campaign, User $user, string $code): DrawTicket
    {
        $grant = TicketGrant::query()->create([
            'campaign_id' => $campaign->getKey(),
            'user_id' => $user->getKey(),
            'quantity' => 1,
            'idempotency_key' => 'test:grant:' . $user->getKey(),
            'idempotency_fingerprint' => hash('sha256', 'grant:' . $user->getKey()),
            'issued_at' => now(),
        ]);

        return DrawTicket::query()->create([
            'campaign_id' => $campaign->getKey(),
            'ticket_grant_id' => $grant->getKey(),
            'user_id' => $user->getKey(),
            'code' => $code,
            'status' => DrawTicketStatusEnum::ACTIVE,
            'issued_at' => now(),
        ]);
    }
}
