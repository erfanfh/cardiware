<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Identity\Models\User;
use App\Infrastructure\Services\Setting\Groups\AffiliateSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * هر کاربر در اولین ورود به پنل، یک رکورد همکاری با کد یکتا می‌گیرد.
 * تنظیمات پیش‌فرض (کمیسیون، پنجره انتساب و...) از تنظیمات سراسری می‌آید.
 */
class EnsureUserAffiliateAction
{
    public function __construct(
        private readonly AffiliateSettings $settings,
    ) {
    }

    public function execute(User $user): Affiliate
    {
        $existing = Affiliate::query()->where('user_id', $user->getKey())->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($user): Affiliate {
            $existing = Affiliate::query()
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            return Affiliate::query()->create([
                'user_id' => $user->getKey(),
                'code' => $this->generateUniqueCode(),
                'is_active' => $this->settings->isEnabled(),
                'commission_type' => $this->settings->defaultCommissionType(),
                'commission_value' => $this->settings->defaultCommissionValue(),
                'cookie_days' => $this->settings->defaultCookieDays(),
                'attribution_days' => $this->settings->defaultAttributionDays(),
                'min_withdrawal_amount' => $this->settings->defaultMinWithdrawalAmount(),
            ]);
        });
    }

    private function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            $code = 'MW' . strtoupper(substr(str_shuffle(str_repeat($alphabet, 8)), 0, 6));

            $exists = Affiliate::query()->where('code', $code)->exists();

            if (! $exists) {
                return $code;
            }
        }

        throw (new ModelNotFoundException())->setModel(Affiliate::class);
    }
}
