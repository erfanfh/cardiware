<?php

namespace App\Application\Vip\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipSubscription;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * اشتراک VIP هدیه، بدون سفارش (پاداش باشگاه یا هدیه مدیر). مثل تمدید عادی به
 * انتهای اشتراک فعلی اضافه می‌شود. gift_key یکتاست، پس هر هدیه فقط یک‌بار ثبت می‌شود.
 */
final class GrantGiftVipSubscriptionAction
{
    public function execute(User $user, int $days, string $source, string $giftKey): ?VipSubscription
    {
        if ($days < 1 || $days > 3650) {
            throw new InvalidArgumentException('Gift VIP duration must be between 1 and 3650 days.');
        }

        return DB::transaction(function () use ($user, $days, $source, $giftKey): ?VipSubscription {
            // هم‌ترتیب با تمدیدهای سفارشی تا دو تمدید هم‌زمان روی هم نیفتند.
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if (VipSubscription::query()->where('gift_key', $giftKey)->exists()) {
                return null;
            }

            $now = CarbonImmutable::now();
            $latestEnd = VipSubscription::query()
                ->where('user_id', $user->getKey())
                ->whereNull('revoked_at')
                ->max('expires_at');
            $start = $latestEnd && CarbonImmutable::parse($latestEnd)->greaterThan($now)
                ? CarbonImmutable::parse($latestEnd)
                : $now;

            return VipSubscription::query()->create([
                'user_id' => $user->getKey(),
                'source' => $source,
                'gift_key' => $giftKey,
                'duration_days' => $days,
                'starts_at' => $start,
                'expires_at' => $start->addDays($days),
            ]);
        }, 3);
    }
}
