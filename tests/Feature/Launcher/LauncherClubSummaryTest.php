<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Actions\GetLauncherClubSummaryAction;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LauncherClubSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
    }

    // تنظیمات در حافظه‌ی ایستا و کش می‌مانند؛ بدون پاک‌کردن، درصد پاداش این
    // آزمون به آزمون‌های بعدی نشت می‌کند.
    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        parent::tearDown();
    }

    public function test_summary_has_points_level_progress_rank_and_vip(): void
    {
        LoyaltyLevel::query()->create(['title' => 'برنز', 'min_xp' => 0, 'color_key' => 'amber', 'sort_order' => 1, 'is_active' => true]);
        LoyaltyLevel::query()->create(['title' => 'نقره', 'min_xp' => 1000, 'color_key' => 'slate', 'sort_order' => 2, 'is_active' => true]);
        app(UpdateSettingAction::class)->execute('loyalty', 'vip_points_bonus_percent', 50);

        $other = User::factory()->create();
        $this->account($other, 900);
        $user = User::factory()->create();
        $this->account($user, 400);
        $this->giveVip($user);

        $data = app(GetLauncherClubSummaryAction::class)->execute($user);

        $this->assertSame(400, $data['points']['balance']);
        $this->assertSame('برنز', $data['level']['title']);
        $this->assertSame(1, $data['level']['number']);
        $this->assertSame(2, $data['level']['total']);
        $this->assertSame('نقره', $data['level']['next_title']);
        $this->assertSame(600, $data['level']['xp_to_next']);
        $this->assertSame(40, $data['level']['progress_percent']);
        $this->assertSame(2, $data['rank']['position']);
        $this->assertSame(2, $data['rank']['members']);
        $this->assertTrue($data['vip']['active']);
        $this->assertSame(20, $data['vip']['days_left']);
        $this->assertSame(50, $data['vip']['points_bonus_percent']);
    }

    public function test_new_member_without_points_or_vip(): void
    {
        $data = app(GetLauncherClubSummaryAction::class)->execute(User::factory()->create());

        $this->assertSame(0, $data['points']['balance']);
        $this->assertNull($data['rank']['position']);
        $this->assertFalse($data['vip']['active']);
        $this->assertNull($data['vip']['expires_at']);
        $this->assertSame(0, $data['vip']['points_bonus_percent']);
    }

    public function test_route_is_registered_behind_launcher_auth(): void
    {
        $route = Route::getRoutes()->getByName('api.v1.launcher.club');

        $this->assertNotNull($route);
        $this->assertContains('auth:sanctum', $route->gatherMiddleware());
        $this->assertContains('launcher.device', $route->gatherMiddleware());
    }

    private function account(User $user, int $points): void
    {
        PointAccount::query()->create(['user_id' => $user->id])
            ->forceFill(['balance' => $points, 'lifetime_earned' => $points, 'lifetime_xp' => $points])
            ->save();
    }

    private function giveVip(User $user): void
    {
        DB::statement('PRAGMA defer_foreign_keys = ON');
        DB::table('vip_subscriptions')->insert([
            'user_id' => $user->id, 'vip_plan_id' => 1, 'order_id' => 1, 'order_item_id' => $user->id,
            'duration_days' => 30, 'starts_at' => now()->subDays(10), 'expires_at' => now()->addDays(20),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
