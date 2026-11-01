<?php

namespace App\Application\Affiliate\Actions;

use App\Application\Affiliate\DTOs\AffiliateAttributionResult;
use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Domain\Affiliate\Enums\AffiliateOrderSourceEnum;
use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Affiliate\Models\AffiliateOrder;
use App\Domain\Affiliate\Models\AffiliateReferral;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Promotion\Models\CouponRedemption;
use App\Infrastructure\Services\Setting\Groups\AffiliateSettings;
use Illuminate\Support\Facades\DB;

/**
 * انتساب سفارش پرداخت‌شده به همکار و ثبت کمیسیون.
 *
 * دو مسیر انتساب دارد:
 *  ۱) کد تخفیف: اگر سفارش با کد کوپنی که کد همکار است خریداری شده باشد.
 *  ۲) لینک معرفی: اگر خریدار با لینک ?ref= همکار ثبت‌نام کرده و هنوز داخل پنجره انتساب باشد.
 *
 * ایده‌مپوتنت است (order_id یکتا) و برای اولین خریدِ کاربرِ معرفی‌شده، پاداش امتیازی
 * معرفی (به معرف و معرف‌شده) هم می‌پردازد.
 */
class AttributeAffiliateOrderAction
{
    public function __construct(
        private readonly AffiliateSettings $settings,
    ) {
    }

    public function execute(Order $order): ?AffiliateAttributionResult
    {
        if (! $this->settings->isEnabled()) {
            return null;
        }

        $user = $order->user;

        if ($user === null || $order->isPaid() === false) {
            return null;
        }

        if (AffiliateOrder::query()->where('order_id', $order->getKey())->exists()) {
            return null;
        }

        [$affiliate, $source] = $this->resolveAffiliate($order);

        if ($affiliate === null || $affiliate->user_id === $user->getKey()) {
            return null;
        }

        $result = DB::transaction(function () use ($order, $user, $affiliate, $source): ?AffiliateAttributionResult {
            // قفل و بررسی دوباره برای جلوگیری از ثبت موازی
            $already = AffiliateOrder::query()
                ->where('order_id', $order->getKey())
                ->lockForUpdate()
                ->exists();

            if ($already) {
                return null;
            }

            $commission = $affiliate->commissionFor((int) $order->total_amount);

            if ($commission <= 0) {
                return null;
            }

            $affiliateOrder = AffiliateOrder::query()->create([
                'affiliate_id' => $affiliate->getKey(),
                'order_id' => $order->getKey(),
                'user_id' => $user->getKey(),
                'order_amount' => (int) $order->total_amount,
                'commission_amount' => $commission,
                'status' => 'active',
                'source' => $source->value,
            ]);

            $bonuses = $source === AffiliateOrderSourceEnum::REFERRAL
                ? $this->awardReferralBonuses($affiliate, $user, $order)
                : ['referrer' => 0, 'referee' => 0];

            return new AffiliateAttributionResult($affiliateOrder, $bonuses['referrer'], $bonuses['referee']);
        });

        return $result;
    }

    /** @return array{0: ?Affiliate, 1: AffiliateOrderSourceEnum} */
    private function resolveAffiliate(Order $order): array
    {
        // ۱) مسیر کد تخفیف
        $redemption = CouponRedemption::query()
            ->where('order_id', $order->getKey())
            ->first();

        if ($redemption !== null && filled($redemption->code)) {
            $affiliate = Affiliate::query()
                ->eligible()
                ->whereRaw('UPPER(code) = ?', [mb_strtoupper(trim($redemption->code))])
                ->first();

            if ($affiliate !== null && $affiliate->user_id !== $order->user_id) {
                return [$affiliate, AffiliateOrderSourceEnum::COUPON];
            }
        }

        // ۲) مسیر لینک معرفی
        $referral = AffiliateReferral::query()
            ->where('referred_user_id', $order->user_id)
            ->first();

        if ($referral !== null) {
            $affiliate = Affiliate::query()
                ->eligible()
                ->whereKey($referral->affiliate_id)
                ->first();

            if ($affiliate !== null) {
                $windowStart = now()->subDays($affiliate->attribution_days);

                if ($referral->created_at === null || $referral->created_at->gte($windowStart)) {
                    return [$affiliate, AffiliateOrderSourceEnum::REFERRAL];
                }
            }
        }

        return [null, AffiliateOrderSourceEnum::REFERRAL];
    }

    /**
     * پاداش معرفی: فقط برای اولین سفارش پرداخت‌شده‌ی کاربر معرفی‌شده.
     *
     * @return array{referrer: int, referee: int}
     */
    private function awardReferralBonuses(Affiliate $affiliate, User $referredUser, Order $order): array
    {
        if (! $this->settings->referralBonusEnabled()) {
            return ['referrer' => 0, 'referee' => 0];
        }

        $paidOrdersCount = $referredUser->orders()->paid()->count();

        if ($paidOrdersCount > 1) {
            return ['referrer' => 0, 'referee' => 0];
        }

        if ((int) $order->total_amount < $this->settings->referralBonusMinOrderAmount()) {
            return ['referrer' => 0, 'referee' => 0];
        }

        $referrerPoints = $this->settings->referralBonusReferrerPoints();
        $refereePoints = $this->settings->referralBonusRefereePoints();
        $awarded = ['referrer' => 0, 'referee' => 0];

        if ($referrerPoints > 0 && $affiliate->user_id !== null) {
            $referrer = $affiliate->user()->first();
            $account = $referrer?->pointAccount()->first();

            if ($referrer !== null && $account !== null) {
                app(AwardPointsAction::class)->execute(new AwardPointsDTO(
                    account: $account,
                    amount: $referrerPoints,
                    reason: 'referral_bonus',
                    idempotencyKey: 'referral-bonus:referrer:' . $referredUser->getKey(),
                    reference: $order,
                    description: 'پاداش معرفی دوست (اولین خرید کاربر معرفی‌شده)',
                    metadata: ['referred_user_id' => $referredUser->getKey()],
                ));

                $awarded['referrer'] = $referrerPoints;
            }
        }

        if ($refereePoints > 0) {
            $account = $referredUser->pointAccount()->first();

            if ($account !== null) {
                app(AwardPointsAction::class)->execute(new AwardPointsDTO(
                    account: $account,
                    amount: $refereePoints,
                    reason: 'referral_bonus',
                    idempotencyKey: 'referral-bonus:referee:' . $referredUser->getKey(),
                    reference: $order,
                    description: 'هدیه اولین خرید با لینک معرفی',
                    metadata: ['affiliate_user_id' => $affiliate->user_id],
                ));

                $awarded['referee'] = $refereePoints;
            }
        }

        return $awarded;
    }
}
