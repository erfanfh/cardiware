<?php

namespace App\Application\Affiliate\Listeners;

use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Affiliate\Models\AffiliateReferral;
use App\Domain\Identity\Events\UserRegisteredEvent;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\AffiliateSettings;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * اتصال کاربر تازه‌ثبت‌نام‌شده به همکاری که او را با لینک ?ref= معرفی کرده است.
 * ضدتقلب: اگر موبایل یا ایمیل معرف و معرف‌شده یکی باشد، اتصال انجام نمی‌شود.
 */
class BindReferredUserListener
{
    public const COOKIE_NAME = 'mw_ref';

    public function __construct(
        private readonly AffiliateSettings $settings,
    ) {
    }

    public function handle(UserRegisteredEvent $event): void
    {
        try {
            if (! $this->settings->isEnabled()) {
                return;
            }

            $affiliateId = (int) request()?->cookie(self::COOKIE_NAME, 0);

            if ($affiliateId <= 0) {
                return;
            }

            $user = User::query()->find($event->userId);

            $affiliate = Affiliate::query()
                ->eligible()
                ->find($affiliateId);

            if ($user === null || $affiliate === null || $affiliate->user_id === $user->getKey()) {
                return;
            }

            $owner = $affiliate->user()->first();

            // ضدتقلب: ثبت‌نام با اطلاعات تماسِ خودِ معرف پاداش ندارد.
            if ($owner !== null
                && (($user->mobile && $user->mobile === $owner->mobile)
                    || (strcasecmp((string) $user->email, (string) $owner->email) === 0))) {
                return;
            }

            DB::table('affiliate_referrals')->insertOrIgnore([
                'affiliate_id' => $affiliate->getKey(),
                'referred_user_id' => $user->getKey(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
