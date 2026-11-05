<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\DTOs\ReversePointEntryDTO;
use App\Application\Loyalty\Exceptions\PointReversalExceededException;
use App\Application\Loyalty\Support\ReawardableLedgerKey;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Support\Facades\App;
use Throwable;

/**
 * وقتی XP کاربر پایین می‌آید (برگشت امتیاز سفارش مرجوعی یا دیدگاه ردشده)،
 * پاداش سطح‌هایی که دیگر به آستانه‌شان نمی‌رسد برگردانده می‌شود. اگر کاربر
 * دوباره به آن سطح برسد، ProcessLevelUpAction پاداش را از نو پرداخت می‌کند.
 */
final class RevokeLevelRewardsAboveXpAction
{
    public function __construct(
        private readonly ResolveUserLevelAction $resolveUserLevel,
        private readonly ReawardableLedgerKey $keys,
    ) {
    }

    /** @throws Throwable */
    public function execute(PointAccount $account): void
    {
        $xp = (int) $account->lifetime_xp;

        foreach ($this->resolveUserLevel->activeLevels() as $level) {
            if ((int) $level->min_xp <= $xp) {
                continue;
            }

            $baseKey = "level-up:{$account->getKey()}:{$level->getKey()}";

            foreach ($this->keys->activeEntries((int) $account->getKey(), $baseKey) as $row) {
                try {
                    // تأخیری: ReversePointEntryAction خودش این کلاس را صدا می‌زند.
                    App::make(ReversePointEntryAction::class)->execute(new ReversePointEntryDTO(
                        entry: $row['entry'],
                        amount: $row['remaining'],
                        idempotencyKey: "level-down:{$row['entry']->getKey()}",
                        description: "برگشت پاداش سطح {$level->title} (کاهش امتیاز سطح)",
                        metadata: ['level_id' => $level->getKey(), 'xp_after' => $xp],
                    ));
                } catch (PointReversalExceededException) {
                    continue;
                }
            }
        }
    }
}
