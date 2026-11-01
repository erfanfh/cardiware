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
 * ساختار: [['days' => 7, 'points' => 200], ...]
 * وقتی تعداد روزهای زنجیره فعلی دقیقاً به days برسد، پاداش یک‌بار برای همین
 * زنجیره (بر اساس تاریخ شروعش) پرداخت می‌شود. با قطع زنجیره، شمارش از یک شروع
 * می‌شود و رسیدن دوباره به مایل‌استون، پاداش جدیدی دارد.
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
     * @return array{streak: LoyaltyStreak, isNewDay: bool, awardedMilestones: array<int, array{days: int, points: int}>}
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
                if ($locked->current_days !== (int) $milestone['days']) {
                    continue;
                }

                $points = (int) ($milestone['points'] ?? 0);
                if ($points <= 0) {
                    continue;
                }

                $entry = $this->awardMilestone($user, $locked, (int) $milestone['days'], $points);

                if ($entry !== null) {
                    $awarded[] = ['days' => (int) $milestone['days'], 'points' => $points];
                }
            }
        });

        return ['streak' => $streak->refresh(), 'isNewDay' => $isNewDay, 'awardedMilestones' => $awarded];
    }

    /**
     * @return array<int, array{days: int, points: int}>
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
            $points = (int) ($item['points'] ?? 0);

            if ($days > 0 && $points > 0) {
                $milestones[] = ['days' => $days, 'points' => $points];
            }
        }

        return $milestones;
    }

    /**
     * @throws Throwable
     */
    private function awardMilestone(User $user, LoyaltyStreak $streak, int $days, int $points): ?PointLedgerEntry
    {
        $startedOn = $streak->current_streak_started_on?->toDateString() ?? 'unknown';
        $idempotencyKey = "streak:{$user->getKey()}:{$startedOn}:{$days}";

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
            description: "پاداش زنجیره {$days} روزه ورود روزانه",
            metadata: [
                'streak_days' => $days,
                'streak_started_on' => $startedOn,
            ],
        ));
    }
}
