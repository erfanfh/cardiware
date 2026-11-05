<?php

namespace Tests\Feature\Loyalty;

use App\Application\Loyalty\Actions\AdjustPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AdjustPointsDTO;
use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\CommentEarningContext;
use App\Application\Loyalty\Streak\Actions\RecordDailyStreakAction;
use App\Application\System\Actions\UpdateSettingAction;
use App\Domain\Content\Models\UserMediaSubmission;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use App\Domain\Vip\Models\VipSubscription;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Infrastructure\Services\Setting\BaseSettings;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * قوانین فاز ۱ باشگاه: زنجیره‌ی تکرارشونده با VIP هدیه، امتیاز کاهشی رسانه،
 * حذف فروشنده/کارمند از امتیاز دیدگاه، و اصلاح دستی XP توسط مدیر.
 */
class LoyaltyPhaseOneRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        parent::tearDown();
    }

    public function test_streak_rewards_repeat_and_ninety_days_gives_a_month_of_vip(): void
    {
        app(UpdateSettingAction::class)->execute('loyalty', 'streak_milestones', [
            ['days' => 7, 'points' => 50, 'repeat' => true],
            ['days' => 30, 'points' => 500, 'repeat' => true],
            ['days' => 90, 'points' => 0, 'vip_days' => 30, 'repeat' => true],
        ]);
        $user = User::factory()->create();
        $streak = app(RecordDailyStreakAction::class);
        $start = CarbonImmutable::parse('2026-01-01 10:00', config('app.timezone'));

        for ($day = 0; $day < 90; $day++) {
            $streak->execute($user, $start->addDays($day));
        }

        // ۱۲ بار هر ۷ روز (۷..۸۴) + ۳ بار هر ۳۰ روز، بدون قطع پاداش بعد از روز ۳۰.
        $this->assertSame(12 * 50 + 3 * 500, (int) PointAccount::query()->where('user_id', $user->id)->value('balance'));
        $gift = VipSubscription::query()->where('user_id', $user->id)->sole();
        $this->assertSame('loyalty_streak', $gift->source);
        $this->assertSame(30, $gift->duration_days);
        $this->assertNull($gift->order_id);

        // دوباره ثبت همان روز پاداش تکراری نمی‌دهد.
        $streak->execute($user, $start->addDays(89));
        $this->assertSame(1, VipSubscription::query()->where('user_id', $user->id)->count());
    }

    public function test_gallery_points_decrease_and_stop_at_the_monthly_cap(): void
    {
        app(UpdateSettingAction::class)->execute('gallery', 'image_points', 300);
        app(UpdateSettingAction::class)->execute('gallery', 'monthly_rewarded_limit', 3);
        $admin = $this->staffUser();
        $user = User::factory()->create();
        $this->actingAs($admin)->withoutMiddleware(EnsureUserIsAdmin::class);

        $awarded = [];
        foreach (range(1, 4) as $n) {
            $submission = UserMediaSubmission::query()->create([
                'user_id' => $user->id, 'type' => 'image', 'title' => "Shot {$n}",
                'image_url' => 'https://example.test/' . $n . '.jpg', 'status' => 'pending',
            ]);
            $this->patch(route('admin.gallery.status', $submission), ['status' => 'approved'])->assertRedirect();
            $awarded[] = (int) $submission->fresh()->points_awarded;
        }

        // اولی کامل، دومی ۵۰٪، سومی ۲۵٪، چهارمی سقف ماهانه.
        $this->assertSame([300, 150, 75, 0], $awarded);

        // لغو تأیید، امتیاز را برمی‌گرداند.
        $first = UserMediaSubmission::query()->where('title', 'Shot 1')->firstOrFail();
        $this->patch(route('admin.gallery.status', $first), ['status' => 'rejected'])->assertRedirect();
        $this->assertNull($first->fresh()->points_awarded);
        $this->assertSame(225, (int) PointAccount::query()->where('user_id', $user->id)->value('balance'));
    }

    public function test_sellers_and_staff_get_no_comment_points(): void
    {
        EarningRule::query()->create([
            'code' => 'test-comment', 'title' => 'Comment', 'type' => EarningRuleTypeEnum::COMMENT,
            'is_active' => true, 'priority' => 100, 'config' => ['points' => 50, 'daily_limit' => 2],
        ]);

        $regular = User::factory()->create();
        $seller = User::factory()->create();
        DB::statement('PRAGMA defer_foreign_keys = ON');
        $storeId = $this->insertRow('stores', ['title' => 'Test store', 'slug' => 'test-store-' . uniqid(), 'created_at' => now(), 'updated_at' => now()]);
        DB::table('store_users')->insert(['store_id' => $storeId, 'user_id' => $seller->id, 'created_at' => now(), 'updated_at' => now()]);
        $staff = $this->staffUser();

        $results = [];
        foreach ([$regular, $seller, $staff] as $user) {
            $comment = $this->approvedComment($user);
            $results[] = app(ProcessEarningRuleAction::class)
                ->execute(EarningRuleTypeEnum::COMMENT, new CommentEarningContext($user, $comment))
                ->entry?->amount;
        }

        $this->assertSame([50, null, null], array_map(fn($v) => $v === null ? null : (int) $v, $results));
    }

    public function test_admin_xp_adjustment_moves_the_level_and_its_reward(): void
    {
        LoyaltyLevel::query()->create(['title' => 'تازه‌وارد', 'min_xp' => 0, 'reward_points' => 0, 'purchase_multiplier' => 1, 'color_key' => 'slate', 'sort_order' => 1, 'is_active' => true]);
        LoyaltyLevel::query()->create(['title' => 'بازیکن', 'min_xp' => 1000, 'reward_points' => 200, 'purchase_multiplier' => 1, 'color_key' => 'cyan', 'sort_order' => 2, 'is_active' => true]);
        $user = User::factory()->create();
        $account = app(GetOrCreatePointAccountAction::class)->execute($user);
        $adjust = app(AdjustPointsAction::class);

        $adjust->execute(new AdjustPointsDTO($account, PointEntryDirectionEnum::CREDIT, 1000, 'manual_xp_adjustment', 'adj-1', affectsXp: true));
        $this->assertSame(1000, (int) $account->fresh()->lifetime_xp);
        $this->assertSame(1200, (int) $account->fresh()->balance); // + پاداش سطح

        $adjust->execute(new AdjustPointsDTO($account, PointEntryDirectionEnum::DEBIT, 500, 'manual_xp_adjustment', 'adj-2', affectsXp: true));
        $this->assertSame(500, (int) $account->fresh()->lifetime_xp);
        $this->assertSame(500, (int) $account->fresh()->balance); // پاداش سطح برگشت خورد

        // اصلاح فقط موجودی، XP را دست نمی‌زند.
        $adjust->execute(new AdjustPointsDTO($account, PointEntryDirectionEnum::CREDIT, 100, 'manual_adjustment', 'adj-3'));
        $this->assertSame(500, (int) $account->fresh()->lifetime_xp);
        $this->assertSame(2, PointLedgerEntry::query()->where('reason', 'manual_xp_adjustment')->count());
    }

    private function approvedComment(User $user): Comment
    {
        $comment = new Comment();
        $comment->forceFill([
            'commentable_type' => 'post',
            'commentable_id' => 1,
            'user_id' => $user->id,
            'content' => 'نظر تستی',
            'status' => CommentStatusEnum::APPROVED,
            'approved_at' => now(),
        ])->save();

        return $comment;
    }

    private function staffUser(): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'title' => 'Staff ' . uniqid(), 'is_super_admin' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return User::factory()->create(['role_id' => $roleId]);
    }

    /** درج ردیف با پر کردن ستون‌های NOT NULL بی‌ربط به آزمون. */
    private function insertRow(string $table, array $values): int
    {
        foreach (DB::select("PRAGMA table_info('{$table}')") as $column) {
            if ($column->pk || ! $column->notnull || $column->dflt_value !== null || array_key_exists($column->name, $values)) {
                continue;
            }
            $type = strtolower((string) $column->type);
            $values[$column->name] = match (true) {
                str_contains($type, 'int'), str_contains($type, 'numeric'), str_contains($type, 'real') => 0,
                str_contains($type, 'date'), str_contains($type, 'time') => now(),
                default => '',
            };
        }

        return (int) DB::table($table)->insertGetId($values);
    }
}
