<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Loyalty\Enums\PointEntryDirectionEnum;
use App\Domain\Loyalty\Enums\PointEntryTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\PointLedgerEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * انقضای امتیاز کاربران غیرفعال.
 *
 * مدل توافق‌شده: امتیازها به‌خاطر «عدم فعالیت» منقضی می‌شوند نه با سن؛
 * اگر کاربر بیش از N ماه (Setting «loyalty.points_inactivity_expiry_months»،
 * پیش‌فرض ۱۲، صفر = خاموش) هیچ گردش امتیازی نداشته باشد، کل موجودی‌اش با
 * یک ردیف EXPIRATION صفر می‌شود. lifetime_spent تغییر نمی‌کند چون مصرف
 * واقعی نیست. با اولین فعالیت بعدی حساب دوباره سالم است.
 */
final class ExpireInactivePointsAction
{
    public function __construct(
        private readonly ResolveUserLevelAction $resolveUserLevel,
    ) {
    }

    /**
     * @return array<int, PointLedgerEntry> ردیف‌های انقضای ایجادشده
     */
    public function execute(?CarbonImmutable $at = null): array
    {
        $months = (int) get_setting('loyalty', 'points_inactivity_expiry_months', 12);

        if ($months <= 0) {
            return [];
        }

        $at ??= now(config('app.timezone'))->toImmutable();
        $cutoff = $at->subMonths($months);

        $accounts = PointAccount::query()
            ->where('balance', '>', 0)
            ->whereDoesntHave('entries', fn ($query) => $query->where('occurred_at', '>', $cutoff))
            ->get();

        $entries = [];

        foreach ($accounts as $account) {
            $entry = $this->expireAccount($account, $at);

            if ($entry !== null) {
                $entries[] = $entry;
                Log::info('Loyalty points expired due to inactivity.', [
                    'user_id' => $account->user_id,
                    'amount' => $entry->amount,
                ]);
            }
        }

        return $entries;
    }

    private function expireAccount(PointAccount $account, CarbonImmutable $at): ?PointLedgerEntry
    {
        return DB::transaction(function () use ($account, $at): ?PointLedgerEntry {
            /** @var PointAccount $locked */
            $locked = PointAccount::query()
                ->whereKey($account->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $locked->balance <= 0) {
                return null;
            }

            // اگر دقیقاً در همین لحظه فعالیت تازه‌ای رسیده، انقضا بی‌اعتبار است.
            $hasRecentEntry = PointLedgerEntry::query()
                ->where('point_account_id', $locked->getKey())
                ->where('occurred_at', '>', $at->copy()->subMonths((int) get_setting('loyalty', 'points_inactivity_expiry_months', 12)))
                ->exists();

            if ($hasRecentEntry) {
                return null;
            }

            $idempotencyKey = 'expire-inactivity:' . $locked->getKey() . ':' . $at->toDateString();

            $already = PointLedgerEntry::query()
                ->where('idempotency_key', $idempotencyKey)
                ->exists();

            if ($already) {
                return null;
            }

            $amount = (int) $locked->balance;
            $balanceBefore = $amount;
            $balanceAfter = 0;

            $locked->forceFill(['balance' => 0])->save();

            return PointLedgerEntry::query()->create([
                'point_account_id' => $locked->getKey(),
                'type' => PointEntryTypeEnum::EXPIRATION,
                'direction' => PointEntryDirectionEnum::DEBIT,
                'reason' => 'inactivity_expiry',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'idempotency_key' => $idempotencyKey,
                'idempotency_fingerprint' => hash('sha256', $idempotencyKey),
                'description' => 'انقضای امتیاز به دلیل عدم فعالیت طولانی',
                'occurred_at' => $at,
                'metadata' => [
                    'months' => (int) get_setting('loyalty', 'points_inactivity_expiry_months', 12),
                ],
            ]);
        });
    }
}
