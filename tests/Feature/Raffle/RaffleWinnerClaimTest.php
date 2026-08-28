<?php

namespace Tests\Feature\Raffle;

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

class RaffleWinnerClaimTest extends TestCase
{
    use RefreshDatabase;

    public function test_winner_can_claim_prize_and_delivery_snapshot_is_persisted(): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);
        $winner = $this->winner($user);

        $response = $this->actingAs($user)->post(route('account.raffles.winners.claim', $winner), [
            'contact_name' => 'کاربر نمونه',
            'mobile' => '09123456789',
            'postal_code' => '1234567890',
            'address' => 'اصفهان، خیابان نمونه، پلاک ده',
            'accept_rules' => '1',
        ]);

        $response->assertRedirect(route('account.raffles'));
        $winner->refresh();
        $this->assertSame(DrawWinnerStatusEnum::CONFIRMED, $winner->status);
        $this->assertNotNull($winner->claimed_at);
        $this->assertSame('کاربر نمونه', $winner->claim_contact_name);
        $this->assertSame('1234567890', $winner->claim_postal_code);
    }

    public function test_another_user_cannot_claim_the_winner_record(): void
    {
        $winner = $this->winner(User::factory()->create());

        $this->actingAs(User::factory()->create())
            ->post(route('account.raffles.winners.claim', $winner), [
                'contact_name' => 'مهاجم',
                'mobile' => '09123456789',
                'address' => 'نشانی وارد شده توسط کاربر دیگر',
                'accept_rules' => '1',
            ])
            ->assertNotFound();

        $this->assertSame(DrawWinnerStatusEnum::PENDING, $winner->fresh()->status);
    }

    private function winner(User $user): DrawWinner
    {
        $campaign = DrawCampaign::query()->create([
            'title' => 'قرعه‌کشی تست دریافت',
            'slug' => 'claim-test',
            'status' => DrawCampaignStatusEnum::COMPLETED,
            'reserve_winners_per_prize' => 0,
            'winner_confirmation_window_hours' => 72,
        ]);
        $prize = DrawPrize::query()->create([
            'campaign_id' => $campaign->id,
            'title' => 'جایزه تست',
            'quantity' => 1,
            'sort_order' => 0,
        ]);
        $grant = TicketGrant::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'quantity' => 1,
            'idempotency_key' => 'claim:'.$user->id,
            'idempotency_fingerprint' => hash('sha256', 'claim:'.$user->id),
            'issued_at' => now(),
        ]);
        $ticket = DrawTicket::query()->create([
            'campaign_id' => $campaign->id,
            'ticket_grant_id' => $grant->id,
            'user_id' => $user->id,
            'code' => 'CLAIM-'.$user->id,
            'status' => DrawTicketStatusEnum::ACTIVE,
            'issued_at' => now(),
        ]);
        $run = DrawRun::query()->create([
            'campaign_id' => $campaign->id,
            'type' => DrawRunTypeEnum::INITIAL,
            'status' => DrawRunStatusEnum::COMPLETED,
            'algorithm' => 'test-v1',
        ]);

        return DrawWinner::query()->create([
            'campaign_id' => $campaign->id,
            'draw_run_id' => $run->id,
            'prize_id' => $prize->id,
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'kind' => DrawWinnerKindEnum::PRIMARY,
            'position' => 1,
            'status' => DrawWinnerStatusEnum::PENDING,
            'selected_at' => now(),
            'confirmation_deadline_at' => now()->addDay(),
        ]);
    }
}
