<?php

namespace App\Application\Raffle\Actions;

use App\Domain\Affiliate\Enums\AffiliateOrderStatusEnum;
use App\Domain\Content\Models\UserMediaSubmission;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Models\DrawCampaign;

final class CheckRaffleEligibilityAction
{
    private const LABELS = [
        'min_referrals' => 'ثبت‌نام دست‌کم :count دوست با لینک دعوت شما',
        'min_referral_orders' => 'دست‌کم :count خرید موفق توسط دعوت‌شدگان',
        'min_own_orders' => 'دست‌کم :count خرید موفق',
        'min_xp' => 'دست‌کم :count امتیاز تجربه در باشگاه',
        'min_comments' => 'دست‌کم :count دیدگاه تأییدشده',
        'min_approved_media' => 'دست‌کم :count رسانهٔ تأییدشده در گالری',
        'min_account_days' => 'دست‌کم :count روز از ساخت حساب گذشته باشد',
        'require_verified_mobile' => 'شماره موبایل تأییدشده',
        'require_vip' => 'اشتراک VIP فعال',
    ];

    public static function activeRules(array $rules): array
    {
        return array_filter(array_map('intval', array_intersect_key($rules, self::LABELS)), fn (int $value) => $value > 0);
    }

    /** @return array{eligible: bool, rules: list<array{label: string, passed: bool, current: ?int, required: int}>} */
    public function execute(DrawCampaign $campaign, ?User $user): array
    {
        $results = [];
        $eligible = true;

        foreach (self::activeRules($campaign->eligibility_rules ?? []) as $key => $required) {
            $current = $user ? $this->progress($key, $user) : null;
            $passed = $current !== null && $current >= $required;
            $eligible = $eligible && $passed;

            $results[] = [
                'label' => str_replace(':count', persian_number($required), self::LABELS[$key]),
                'passed' => $passed,
                'current' => $current,
                'required' => $required,
            ];
        }

        return ['eligible' => $eligible, 'rules' => $results];
    }

    private function progress(string $key, User $user): int
    {
        return match ($key) {
            'min_referrals' => $user->affiliateRecord?->referrals()->count() ?? 0,
            'min_referral_orders' => $user->affiliateRecord?->orders()
                ->where('status', AffiliateOrderStatusEnum::ACTIVE)
                ->whereHas('order', fn ($query) => $query->paid())
                ->count() ?? 0,
            'min_own_orders' => $user->orders()->paid()->count(),
            'min_xp' => (int) $user->pointAccount?->lifetime_xp,
            'min_comments' => Comment::query()->where('user_id', $user->getKey())
                ->where('status', CommentStatusEnum::APPROVED)->count(),
            'min_approved_media' => UserMediaSubmission::query()->where('user_id', $user->getKey())->approved()->count(),
            'min_account_days' => $user->created_at ? max(0, (int) $user->created_at->diffInDays(now())) : 0,
            'require_verified_mobile' => (int) ($user->mobile_verified_at !== null),
            'require_vip' => (int) $user->isVipMember(),
        };
    }
}
