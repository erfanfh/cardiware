<?php

namespace App\Application\Loyalty\Streak\Actions;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\LoyaltyStreak;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * به‌روزرسانی زنجیره ورود روزانه و پرداخت پاداش مایل‌استون‌ها.
 *
 * «مایل‌استون»ها از Setting «loyalty.streak_milestones» خوانده می‌شوند؛
 * ساختار: [['days' => 7, 'points' => 50, 'repeat' => true, 'vip_days' => 0], ...]
 *  - repeat=false: فقط وقتی روزهای زنجیره دقیقاً به days برسد (یک‌بار در هر زنجیره).
 *  - repeat=true: در هر مضرب days (۷، ۱۴، ۲۱...) تا کسی که بی‌وقفه می‌آید بعد از
 *    روز ۳۰ بی‌پاداش نماند و شکستن عمدی زنجیره سودی نداشته باشد.
 *  - vip_days: اشتراک VIP هدیه (مثلاً ۹۰ روز پشت‌سرهم = یک ماه VIP).
 * با قطع زنجیره، شمارش از یک شروع می‌شود.
 */
final class RecordDailyStreakAction
{
    public function __construct(
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccount,
        private readonly AwardPointsAction $awardPoints,
    ) {
    }

    /**
     * @param User $user
     * @param CarbonInterface|null $date تاریخ بازدید (برای تست)
     * @return array{streak: LoyaltyStreak, isNewDay: bool, awardedMilestones: array<int, array{days: int, points: int, vip_days: int}>}
     *
     * @throws Throwable
     */
    public function execute(User $user, ?CarbonInterface $date = null): array
    {
        $date = CarbonImmutable::instance($date ?? now(config('app.timezone')));
        $today = $date->toDateString();
        $yesterday = $date->subDay()->toDateString();

        /** @var LoyaltyStreak $streak */
        $streak = LoyaltyStreak::query()->firstOrCreate(['user_id' => $user->getKey()]);

        if ($streak->last_visit_date?->toDateString() === $today) {
            return ['streak' => $streak, 'isNewDay' => false, 'awardedMilestones' => []];
        }

        $awarded = [];
        $isNewDay = false;

        DB::transaction(function () use ($today, $yesterday, $user, &$awarded, &$streak, &$isNewDay): void {
            /** @var LoyaltyStreak $locked */
            $locked = LoyaltyStreak::query()
                ->whereKey($streak->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->last_visit_date?->toDateString() === $today) {
                $streak = $locked;
                return;
            }

            $continues = $locked->last_visit_date?->toDateString() === $yesterday;

            $locked->current_days = $continues ? $locked->current_days + 1 : 1;
            $locked->longest_days = max($locked->longest_days, $locked->current_days);
            $locked->last_visit_date = $today;
            $locked->current_streak_started_on = $continues
                ? $locked->current_streak_started_on ?? $today
                : $today;
            $locked->save();

            $streak = $locked;
            $isNewDay = true;

            foreach ($this->milestones() as $milestone) {
                $days = (int) $milestone['days'];
                $reached = $milestone['repeat']
                    ? $locked->current_days % $days === 0
                    : $locked->current_days === $days;

                if (! $reached) {
                    continue;
                }

                $entry = $milestone['points'] > 0
                    ? $this->awardMilestone($user, $locked, $milestone)
                    : null;
                $vip = $milestone['vip_days'] > 0
                    ? $this->grantVip($user, $locked, $milestone)
                    : null;

                if ($entry !== null || $vip !== null) {
                    $awarded[] = [
                        'days' => $locked->current_days,
                        'points' => $entry !== null ? $milestone['points'] : 0,
                        'vip_days' => $vip !== null ? $milestone['vip_days'] : 0,
                    ];
                }
            }
        });

        return ['streak' => $streak->refresh(), 'isNewDay' => $isNewDay, 'awardedMilestones' => $awarded];
    }

    /**
     * @return array<int, array{days: int, points: int, repeat: bool, vip_days: int}>
     */
    public function milestones(): array
    {
        $raw = get_setting('loyalty', 'streak_milestones', []);

        if (! is_array($raw)) {
            return [];
        }

        $milestones = [];

        foreach ($raw as $item) {
            $days = (int) ($item['days'] ?? 0);
            $points = max(0, (int) ($item['points'] ?? 0));
            $vipDays = max(0, (int) ($item['vip_days'] ?? 0));

            if ($days > 0 && ($points > 0 || $vipDays > 0)) {
                $milestones[] = [
                    'days' => $days,
                    'points' => $points,
                    'repeat' => filter_var($item['repeat'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'vip_days' => $vipDays,
                ];
            }
        }

        return $milestones;
    }

    /**
     * @throws Throwable
     */
    /**
     * @param array{days: int, points: int, repeat: bool, vip_days: int} $milestone
     */
    private function awardMilestone(User $user, LoyaltyStreak $streak, array $milestone): ?PointLedgerEntry
    {
        $startedOn = $streak->current_streak_started_on?->toDateString() ?? 'unknown';
        $days = (int) $streak->current_days;
        $points = $milestone['points'];
        // کلید قدیمی برای مایل‌استون یک‌باره حفظ شده تا پاداش‌های قبلی دوباره داده نشوند.
        $idempotencyKey = $milestone['repeat']
            ? "streak:{$user->getKey()}:{$startedOn}:{$milestone['days']}:{$days}"
            : "streak:{$user->getKey()}:{$startedOn}:{$days}";

        $already = PointLedgerEntry::query()
            ->where('point_account_id', $this->getOrCreatePointAccount->execute($user)->getKey())
            ->where('idempotency_key', $idempotencyKey)
            ->exists();

        if ($already) {
            return null;
        }

        return $this->awardPoints->execute(new AwardPointsDTO(
            account: $this->getOrCreatePointAccount->execute($user),
            amount: $points,
            reason: 'streak_milestone',
            idempotencyKey: $idempotencyKey,
            description: "پاداش {$days} روز ورود پشت‌سرهم",
            metadata: [
                'streak_days' => $days,
                'streak_started_on' => $startedOn,
            ],
        ));
    }

    /**
     * @param array{days: int, points: int, repeat: bool, vip_days: int} $milestone
     */
    private function grantVip(User $user, LoyaltyStreak $streak, array $milestone): ?\App\Domain\Vip\Models\VipSubscription
    {
        $startedOn = $streak->current_streak_started_on?->toDateString() ?? 'unknown';

        return app(\App\Application\Vip\Actions\GrantGiftVipSubscriptionAction::class)->execute(
            $user,
            $milestone['vip_days'],
            'loyalty_streak',
            "streak-vip:{$user->getKey()}:{$startedOn}:{$milestone['days']}:{$streak->current_days}",
        );
    }
}
