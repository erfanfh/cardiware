<?php

namespace App\Application\Loyalty\Support;

use App\Domain\Loyalty\Models\PointLedgerEntry;
use Illuminate\Support\Collection;

/**
 * کلید idempotency برای پاداش‌هایی که پس از برگشت کامل دوباره قابل کسب‌اند
 * (پاداش سطح، مأموریت، نشان). کلید پایه یک‌بار مصرف است؛ وقتی پاداش قبلی
 * کاملاً برگشت خورده باشد (مثلاً بعد از مرجوعی سفارش)، کلید «:rN» بعدی
 * ساخته می‌شود تا کسب دوباره پرداخت شود ولی پرداخت تکراری هرگز ممکن نباشد.
 */
final class ReawardableLedgerKey
{
    /**
     * کلید قابل استفاده برای پرداخت تازه، یا null اگر پاداشی فعال (برگشت‌نخورده)
     * با همین کلید پایه وجود دارد.
     */
    public function resolve(int $accountId, string $baseKey): ?string
    {
        $entries = $this->entries($accountId, $baseKey);

        if ($entries->isEmpty()) {
            return $baseKey;
        }

        if ($entries->contains(fn(PointLedgerEntry $entry): bool => $this->remaining($entry) > 0)) {
            return null;
        }

        return $baseKey . ':r' . $entries->count();
    }

    /**
     * ردیف‌های پاداشِ هنوز برگشت‌نخورده با این کلید پایه، همراه مقدار باقی‌مانده.
     *
     * @return Collection<int, array{entry: PointLedgerEntry, remaining: int}>
     */
    public function activeEntries(int $accountId, string $baseKey): Collection
    {
        return $this->entries($accountId, $baseKey)
            ->map(fn(PointLedgerEntry $entry): array => ['entry' => $entry, 'remaining' => $this->remaining($entry)])
            ->filter(fn(array $row): bool => $row['remaining'] > 0)
            ->values();
    }

    /** @return Collection<int, PointLedgerEntry> */
    private function entries(int $accountId, string $baseKey): Collection
    {
        return PointLedgerEntry::query()
            ->where('point_account_id', $accountId)
            ->where(fn($query) => $query
                ->where('idempotency_key', $baseKey)
                ->orWhere('idempotency_key', 'like', $baseKey . ':r%'))
            ->orderBy('id')
            ->get();
    }

    private function remaining(PointLedgerEntry $entry): int
    {
        $reversed = (int) PointLedgerEntry::query()
            ->where('reversal_of_id', $entry->getKey())
            ->sum('amount');

        return max(0, (int) $entry->amount - $reversed);
    }
}
