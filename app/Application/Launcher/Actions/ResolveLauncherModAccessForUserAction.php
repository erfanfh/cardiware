<?php

namespace App\Application\Launcher\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Enums\LauncherModAccessTypeEnum;
use App\Domain\Launcher\Models\LauncherModGrant;
use Illuminate\Support\Collection;

/**
 * لایه‌ی دسترسی هر کاربر را روی خروجی (کش‌شده و مستقل از کاربر) مدها سوار
 * می‌کند؛ چون payload مدها بین کاربران مشترک کش می‌شود، وضعیت قفل/بازشدن
 * هر ماد فقط اینجا و به ازای کاربر محاسبه می‌شود:
 *
 *  - free:      همیشه باز برای همه‌ی دارندگان محصول (چه محصول با خرید تکی
 *               باز شده باشد چه با اشتراک ویژه).
 *  - purchase:  فقط با گرانت دائمی خرید تکی باز است.
 *
 * ماد «اشتراک ویژه» عمداً وجود ندارد: اشتراک فقط خودِ محصول را باز می‌کند؛
 * ماد وابسته به اشتراک بعد از پایان اشتراک، فایل نصب‌شده روی بازی می‌گذاشت.
 *
 * برای مادهای قفل‌شده فایل‌ها، رمز آرشیو و الزامی‌بودن از payload حذف
 * می‌شود تا لانچر چیزی جز کارت و قیمت نبیند.
 */
class ResolveLauncherModAccessForUserAction
{
    /**
     * @param array<int, array<string, mixed>> $mods
     * @return array<int, array<string, mixed>>
     */
    public function execute(User $user, array $mods): array
    {
        $modIds = collect($mods)
            ->filter(static fn(array $mod): bool => ($mod['access']['type'] ?? '') !== LauncherModAccessTypeEnum::FREE->value)
            ->map(static fn(array $mod): int => (int) $mod['id'])
            ->values();

        if ($modIds->isEmpty()) {
            return array_map(
                static fn(array $mod): array => self::withAccess($mod, 'free', false, 'free'),
                $mods,
            );
        }

        /** @var Collection<int, LauncherModGrant> $grants */
        $grants = LauncherModGrant::query()
            ->where('user_id', $user->getKey())
            ->whereIn('launcher_mod_id', $modIds)
            ->get()
            ->keyBy('launcher_mod_id');

        return array_map(function (array $mod) use ($grants): array {
            $type = (string) ($mod['access']['type'] ?? LauncherModAccessTypeEnum::FREE->value);

            if ($type === LauncherModAccessTypeEnum::FREE->value) {
                return self::withAccess($mod, 'free', false, 'free');
            }

            // هر گرانتی (خرید تکی یا vip_permanent قدیمی) دسترسی دائمی است؛ حتی اگر
            // ادمین بعداً نوع دسترسی ماد را عوض کند (هم‌راستا با گیت دانلود).
            $grant = $grants->get((int) $mod['id']);

            if ($grant) {
                return self::withAccess($mod, $type, false, (string) $grant->source);
            }

            if ($type === LauncherModAccessTypeEnum::PURCHASE->value) {
                return self::withAccess($this->stripLockedPackage($mod), 'purchase', true, null);
            }

            // نوع ناشناخته (مثلاً vip از نسخه‌ی قبل که هنوز مهاجرت نشده) = رایگان.
            return self::withAccess($mod, 'free', false, 'free');
        }, $mods);
    }

    /**
     * @param array<string, mixed> $mod
     * @return array<string, mixed>
     */
    private static function withAccess(array $mod, string $type, bool $locked, ?string $unlockSource): array
    {
        $mod['access'] = [
            'type' => $type,
            'locked' => $locked,
            'unlock_source' => $unlockSource,
            'price_toman' => $mod['access']['price_toman'] ?? null,
        ];

        return $mod;
    }

    /**
     * @param array<string, mixed> $mod
     * @return array<string, mixed>
     */
    private static function stripLockedPackage(array $mod): array
    {
        if (isset($mod['package']) && is_array($mod['package'])) {
            $mod['package']['files'] = [];
            $mod['package']['archive_password'] = null;
        }

        // ماد قفل‌شده هرگز نباید به‌صورت الزامی نصب شود.
        $mod['is_required'] = false;

        return $mod;
    }
}
