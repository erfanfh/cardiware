<?php

namespace App\Application\Loyalty\Support;

use App\Application\Loyalty\Actions\ResolveUserLevelAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\LoyaltyLevel;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;

/**
 * «کانون نخبگان»: کاربرانی که به سطح انتخاب‌شده در تنظیمات باشگاه (elite_min_level_id)
 * یا بالاتر رسیده‌اند. در نظرسنجی‌های ویژه درباره‌ی محصولات آینده رأی می‌دهند و نشان
 * عضویت روی پروفایلشان دارند. کارمندان سایت همیشه دسترسی دارند.
 */
final class ClubEliteMembership
{
    public function __construct(
        private readonly LoyaltySettings $settings,
        private readonly ResolveUserLevelAction $levels,
    ) {
    }

    public function level(): ?LoyaltyLevel
    {
        $id = (int) $this->settings->get('elite_min_level_id', 0);

        return $id > 0 ? $this->levels->activeLevels()->firstWhere('id', $id) : null;
    }

    public function enabled(): bool
    {
        return $this->level() !== null;
    }

    public function isMember(?User $user): bool
    {
        $level = $this->level();
        if ($user === null || $level === null) {
            return false;
        }

        if ($user->isLoyaltyStaff()) {
            return true;
        }

        return (int) ($user->pointAccount()->value('lifetime_xp') ?? 0) >= (int) $level->min_xp;
    }
}
