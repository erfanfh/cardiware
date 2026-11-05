<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Loyalty\Support\ReawardableLedgerKey;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * پردازش ارتقای سطح پس از افزایش XP.
 *
 * برای هر سطحی که کاربر تازه از آستانه‌اش گذشته باشد یک‌بار (idempotent)
 * پاداش Point می‌دهد. پاداش سطح XP نمی‌دهد تا زنجیره ارتقای بی‌پایان ساخته نشود.
 *
 * نکته: AwardPointsAction به این کلاس وابسته است؛ برای شکستن وابستگی حلقوی،
 * AwardPointsAction از طریق service container و به‌صورت تأخیری گرفته می‌شود.
 */
final class ProcessLevelUpAction
{
    public function __construct(
        private readonly ResolveUserLevelAction $resolveUserLevel,
    ) {
    }

    /**
     * @param PointAccount $account حسابی که XP آن همین حالا افزایش یافته است.
     * @param int|null $previousXp مقدار XP قبل از افزایش (برای تشخیص عبور از آستانه). null = فقط بررسی از سطح فعلی.
     * @return LoyaltyLevel[] سطوحی که در این فراخوانی پاداش گرفتند.
     *
     * @throws Throwable
     */
    public function execute(PointAccount $account, ?int $previousXp = null): array
    {
        $levels = $this->resolveUserLevel->activeLevels();

        if ($levels->isEmpty()) {
            return [];
        }

        $xp = (int) $account->lifetime_xp;
        $from = $previousXp ?? $xp;

        $awarded = [];

        foreach ($levels as $level) {
            $crossed = $level->min_xp > 0
                && $xp >= $level->min_xp
                && $from < $level->min_xp
                && $level->reward_points > 0;

            if (!$crossed) {
                continue;
            }

            $entry = $this->awardLevelReward($account, $level);

            if ($entry !== null) {
                $awarded[] = $level;
            }
        }

        return $awarded;
    }

    /**
     * @throws Throwable
     */
    private function awardLevelReward(PointAccount $account, LoyaltyLevel $level): ?PointLedgerEntry
    {
        // پاداش هر سطح فقط یک‌بار فعال است؛ اگر با کاهش XP برگشت خورده بود،
        // رسیدن دوباره به سطح آن را با کلید تازه دوباره پرداخت می‌کند.
        $idempotencyKey = App::make(ReawardableLedgerKey::class)
            ->resolve((int) $account->getKey(), "level-up:{$account->getKey()}:{$level->getKey()}");

        if ($idempotencyKey === null) {
            return null;
        }

        /** @var AwardPointsAction $awardPoints */
        $awardPoints = App::make(AwardPointsAction::class);

        return $awardPoints->execute(new AwardPointsDTO(
            account: $account,
            amount: $level->reward_points,
            reason: 'level_up',
            idempotencyKey: $idempotencyKey,
            description: "پاداش رسیدن به سطح {$level->title}",
            metadata: [
                'level_id' => $level->getKey(),
                'level_title' => $level->title,
                'min_xp' => $level->min_xp,
            ],
            awardsXp: false,
        ));
    }
}
