<?php

namespace Tests\Feature\Raffle;

use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Application\Raffle\Exceptions\DrawParticipantIneligibleException;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Http\Middleware\ForceTrailingSlash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VipRaffleTest extends TestCase
{
    use RefreshDatabase;

    public function test_raffle_settings_save_and_are_visible_to_guests(): void
    {
        $this->withoutMiddleware(ForceTrailingSlash::class);
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $payload = [
            'title' => 'قرعه‌کشی VIP',
            'slug' => 'vip-raffle',
            'reserve_winners_per_prize' => 3,
            'points_per_ticket' => 250,
            'ticket_price' => 5000,
            'vip_monthly_ticket' => '1',
            'eligibility_rules' => ['require_vip' => '1', 'min_account_days' => '5', 'min_xp' => '0'],
        ];

        $this->actingAs($admin)->post(route('admin.draw-campaigns.store'), $payload)->assertSessionHasNoErrors();
        $campaign = DrawCampaign::query()->sole();
        $this->assertSame(['require_vip' => 1, 'min_account_days' => 5], $campaign->eligibility_rules);
        $this->assertSame(250, $campaign->points_per_ticket);
        $this->assertSame(5000, $campaign->ticket_price);
        $this->assertTrue($campaign->vip_monthly_ticket);

        $campaign->update(['status' => DrawCampaignStatusEnum::OPEN]);
        $this->get(route('admin.draw-campaigns.edit', $campaign))->assertOk()
            ->assertSee('name="eligibility_rules[require_vip]"', false)
            ->assertSee('name="vip_monthly_ticket"', false)
            ->assertSee('name="ticket_price"', false);

        auth()->logout();
        $this->get(route('raffles.index'))->assertOk()->assertSee('فقط اعضای VIP');
        $this->get(route('raffles.show', $campaign->slug))->assertOk()
            ->assertSee('هدیه ماهانه اعضای VIP')
            ->assertSee('اشتراک VIP فعال');

        $payload['vip_monthly_ticket'] = '0';
        $payload['eligibility_rules'] = ['require_vip' => '0', 'min_account_days' => '0'];
        $this->actingAs($admin)->put(route('admin.draw-campaigns.update', $campaign), $payload)->assertSessionHasNoErrors();
        $campaign->refresh();
        $this->assertSame([], $campaign->eligibility_rules);
        $this->assertFalse($campaign->vip_monthly_ticket);
    }

    public function test_monthly_gift_is_idempotent_and_honors_eligibility_and_limits(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(2));
        $campaign = DrawCampaign::query()->create([
            'title' => 'هدیه VIP',
            'slug' => 'vip-gift',
            'status' => DrawCampaignStatusEnum::OPEN,
            'vip_monthly_ticket' => true,
            'eligibility_rules' => ['require_vip' => true, 'require_verified_mobile' => true],
            'ticket_limit' => 2,
            'ticket_limit_per_user' => 2,
        ]);
        $normal = User::factory()->create(['mobile_verified_at' => now()]);
        $unverified = User::factory()->create(['mobile_verified_at' => null]);
        $vip = User::factory()->create(['mobile_verified_at' => now()]);
        foreach ([$unverified, $vip] as $member) {
            $member->vipSubscriptions()->create([
                'source' => 'gift',
                'duration_days' => 90,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addDays(89),
            ]);
        }

        try {
            app(IssueDrawTicketsAction::class)->execute(new IssueDrawTicketsDTO($campaign, $normal, 1, 'normal-user'));
            $this->fail('A non-VIP user received a ticket for a VIP campaign.');
        } catch (DrawParticipantIneligibleException) {
            $this->assertDatabaseCount('draw_tickets', 0);
        }

        $this->artisan('raffle:grant-vip-monthly-tickets')->assertSuccessful();
        $this->artisan('raffle:grant-vip-monthly-tickets')->assertSuccessful();
        $this->assertDatabaseCount('draw_tickets', 1);
        $this->assertDatabaseHas('draw_tickets', ['campaign_id' => $campaign->id, 'user_id' => $vip->id]);

        $this->travel(1)->month();
        $this->artisan('raffle:grant-vip-monthly-tickets')->assertSuccessful();
        $this->assertDatabaseCount('draw_tickets', 2);
        $this->assertSame(1, $campaign->fresh()->participants_count);

        $this->travel(1)->month();
        $this->artisan('raffle:grant-vip-monthly-tickets')->assertSuccessful();
        $this->assertDatabaseCount('draw_tickets', 2);
    }
}
