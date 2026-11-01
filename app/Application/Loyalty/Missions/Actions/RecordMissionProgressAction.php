<?php

namespace App\Application\Loyalty\Missions\Actions;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use App\Domain\Loyalty\Models\LoyaltyMission;
use App\Domain\Loyalty\Models\LoyaltyMissionProgress;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * ثبت پیشرفت مأموریت‌های دوره‌ای پس از یک رویداد (بازدید/دیدگاه/خرید).
 *
 * مأموریت‌ها هفتگی‌اند؛ کلید دوره با «شنبه‌ی شروع هفته» ساخته می‌شود تا
 * ریست هفتگی بدون نیاز به کرون اتفاق بیفتد. با رسیدن پیشرفت به هدف،
 * پاداش یک‌بار (idempotent) پرداخت و مأموریت همان دوره کامل می‌شود.
 */
final class RecordMissionProgressAction
{
    public function __construct(
        private readonly GetOrCreatePointAccountAction $getOrCreatePointAccount,
        private readonly AwardPointsAction $awardPoints,
    ) {
    }

    /**
     * @param MissionMetricEnum $metric رویدادی که رخ داده
     * @param int $count چند واحد پیشرفت (پیش‌فرض ۱)
     * @return array<int, array{mission: LoyaltyMission, progress: LoyaltyMissionProgress, newlyCompleted: bool}>
     *
     * @throws Throwable
     */
    public function execute(User $user, MissionMetricEnum $metric, int $count = 1): array
    {
        $missions = LoyaltyMission::query()
            ->active()
            ->where('metric', $metric->value)
            ->get();

        if ($missions->isEmpty()) {
            return [];
        }

        $periodKey = $this->currentPeriodKey();
        $results = [];

        foreach ($missions as $mission) {
            $results[] = $this->advance($user, $mission, $periodKey, $count);
        }

        return $results;
    }

    /**
     * کلید دوره فعلی: شنبه‌ی شروع هفته به‌صورت Y-m-d (هفته ایرانی از شنبه).
     */
    public function currentPeriodKey(?CarbonInterface $at = null): string
    {
        $date = CarbonImmutable::instance($at ?? now(config('app.timezone')));

        // شنبه = Saturday؛ در PHP dow: 6 = Saturday
        $daysSinceSaturday = ($date->dayOfWeek + 1) % 7;

        return 'W:' . $date->subDays($daysSinceSaturday)->toDateString();
    }

    /**
     * @return array{mission: LoyaltyMission, progress: LoyaltyMissionProgress, newlyCompleted: bool}
     *
     * @throws Throwable
     */
    private function advance(User $user, LoyaltyMission $mission, string $periodKey, int $count): array
    {
        return DB::transaction(function () use ($user, $mission, $periodKey, $count): array {
            /** @var LoyaltyMissionProgress $progress */
            $progress = LoyaltyMissionProgress::query()
                ->where('loyalty_mission_id', $mission->getKey())
                ->where('user_id', $user->getKey())
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if ($progress === null) {
                $progress = LoyaltyMissionProgress::query()->create([
                    'loyalty_mission_id' => $mission->getKey(),
                    'user_id' => $user->getKey(),
                    'period_key' => $periodKey,
                    'progress_count' => 0,
                ]);
            }

            if ($progress->completed_at !== null) {
                return ['mission' => $mission, 'progress' => $progress, 'newlyCompleted' => false];
            }

            $progress->progress_count += $count;
            $progress->save();

            if ($progress->progress_count < $mission->target_count) {
                return ['mission' => $mission, 'progress' => $progress, 'newlyCompleted' => false];
            }

            $entry = $this->awardReward($user, $mission, $periodKey, $progress);

            $progress->completed_at = now();
            $progress->reward_entry_id = $entry?->getKey();
            $progress->save();

            return ['mission' => $mission, 'progress' => $progress, 'newlyCompleted' => true];
        });
    }

    /**
     * @throws Throwable
     */
    private function awardReward(User $user, LoyaltyMission $mission, string $periodKey, LoyaltyMissionProgress $progress): ?PointLedgerEntry
    {
        if ($mission->reward_points <= 0) {
            return null;
        }

        return $this->awardPoints->execute(new AwardPointsDTO(
            account: $this->getOrCreatePointAccount->execute($user),
            amount: $mission->reward_points,
            reason: 'mission_completed',
            idempotencyKey: "mission:{$mission->getKey()}:{$user->getKey()}:{$periodKey}",
            description: "پاداش مأموریت «{$mission->title}»",
            metadata: [
                'mission_id' => $mission->getKey(),
                'mission_code' => $mission->code,
                'period_key' => $periodKey,
                'target_count' => $mission->target_count,
            ],
        ));
    }
}
