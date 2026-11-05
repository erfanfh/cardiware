<?php

namespace Tests\Feature\Loyalty;

use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * کاربر دارای اشتراک ویژه درصد تنظیم‌شده در پنل را روی امتیاز قوانین اضافه می‌گیرد.
 */
class VipPointsBonusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();

        EarningRule::query()->create([
            'code' => 'test-daily-visit',
            'title' => 'Daily visit',
            'type' => EarningRuleTypeEnum::DAILY_VISIT,
            'is_active' => true,
            'priority' => 100,
            'config' => ['points' => 10],
        ]);
        app(UpdateSettingAction::class)->execute('loyalty', 'vip_points_bonus_percent', 50);
    }

    // تنظیمات در حافظه‌ی ایستا و کش می‌مانند؛ بدون پاک‌کردن، درصد پاداش این
    // آزمون به آزمون‌های بعدی نشت می‌کند.
    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        parent::tearDown();
    }

    public function test_vip_member_gets_the_configured_bonus_and_more_xp(): void
    {
        $user = User::factory()->create();
        $this->giveVip($user);

        $this->actingAs($user)->get(route('loyalty.leaderboard'))->assertSuccessful();

        $entry = PointLedgerEntry::query()->sole();
        $this->assertSame(15, (int) $entry->amount);
        $this->assertSame(50, $entry->metadata['vip_bonus_percent']);
        $this->assertSame(10, $entry->metadata['points_before_vip_bonus']);
        $this->assertSame(15, (int) $user->pointAccount()->value('lifetime_xp'));
    }

    public function test_regular_member_gets_the_base_points(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('loyalty.leaderboard'))->assertSuccessful();

        $entry = PointLedgerEntry::query()->sole();
        $this->assertSame(10, (int) $entry->amount);
        $this->assertArrayNotHasKey('vip_bonus_percent', $entry->metadata ?? []);
    }

    public function test_expired_vip_gets_no_bonus(): void
    {
        $user = User::factory()->create();
        $this->giveVip($user, active: false);

        $this->actingAs($user)->get(route('loyalty.leaderboard'))->assertSuccessful();

        $this->assertSame(10, (int) PointLedgerEntry::query()->sole()->amount);
    }

    private function giveVip(User $user, bool $active = true): void
    {
        // فقط ستون‌های اشتراک برای این آزمون مهم‌اند؛ پلن و سفارش ساختگی‌اند.
        DB::statement('PRAGMA defer_foreign_keys = ON');
        DB::table('vip_subscriptions')->insert([
            'user_id' => $user->id,
            'vip_plan_id' => 1,
            'order_id' => 1,
            'order_item_id' => $user->id,
            'duration_days' => 30,
            'starts_at' => now()->subDays(10),
            'expires_at' => $active ? now()->addDays(20) : now()->subDay(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
