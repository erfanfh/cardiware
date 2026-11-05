<?php

namespace App\Application\Loyalty\Champions;

use App\Application\Loyalty\Actions\AwardPointsAction;
use App\Application\Loyalty\Actions\GetOrCreatePointAccountAction;
use App\Application\Loyalty\DTOs\AwardPointsDTO;
use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Domain\Loyalty\Models\LoyaltyChampion;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Infrastructure\Services\Audit\AuditService;
use App\Infrastructure\Services\Setting\Groups\LoyaltySettings;
use Illuminate\Support\Str;
use Throwable;

/**
 * جایزه‌ی یک برنده: امتیاز قابل خرج (بدون XP تا رتبه‌ی دوره‌های بعد را دستکاری نکند)،
 * کد تخفیف شخصی یک‌بارمصرف، و کد قرعه‌کشی در قرعه‌کشی باشگاه. جایزه‌ی ویژه‌ی سال
 * فقط ثبت می‌شود و مدیر آن را تحویل می‌دهد.
 */
final class GrantChampionPrizeAction
{
    public function __construct(
        private readonly AwardPointsAction $awardPoints,
        private readonly GetOrCreatePointAccountAction $accounts,
        private readonly LoyaltySettings $settings,
    ) {
    }

    /**
     * @param array{points: int, coupon_percent: int, coupon_max_amount: int, coupon_days: int, raffle_tickets: int}|null $prize
     * @throws Throwable
     */
    public function execute(LoyaltyChampion $champion, ?array $prize): void
    {
        $user = $champion->user;
        $changes = [];

        if ($prize !== null && $prize['points'] > 0) {
            $this->awardPoints->execute(new AwardPointsDTO(
                account: $this->accounts->execute($user),
                amount: $prize['points'],
                reason: 'champion_prize',
                idempotencyKey: 'champion-prize:' . $champion->getKey(),
                reference: $champion,
                description: 'جایزه ' . $champion->title(),
                awardsXp: false,
            ));
            $changes['points_awarded'] = $prize['points'];
        }

        if ($prize !== null && $prize['coupon_percent'] > 0) {
            $coupon = $this->createCoupon($champion, $prize);
            $changes['coupon_id'] = $coupon->getKey();
            $changes['coupon_code'] = $coupon->code;
        }

        if ($prize !== null && $prize['raffle_tickets'] > 0) {
            $changes['raffle_tickets'] = $this->issueTickets($champion, $prize['raffle_tickets']);
        }

        if ($champion->grand_prize) {
            $changes['grand_prize_status'] = 'pending';
        }

        if ($changes !== []) {
            $champion->forceFill($changes)->save();
        }
    }

    private function createCoupon(LoyaltyChampion $champion, array $prize): Coupon
    {
        do {
            $code = 'MWC-' . $champion->user_id . '-' . Str::upper(Str::random(8));
        } while (Coupon::query()->where('code', $code)->exists());

        $coupon = Coupon::query()->create([
            'code' => $code,
            'title' => 'جایزه ' . $champion->title(),
            'description' => 'کد تخفیف شخصی برترین‌های باشگاه مخاطبان',
            'type' => CouponTypeEnum::PERCENTAGE,
            'value' => $prize['coupon_percent'],
            'max_discount_amount' => $prize['coupon_max_amount'] > 0 ? $prize['coupon_max_amount'] : null,
            'scope' => CouponScopeEnum::ORDER,
            'status' => CouponStatusEnum::ACTIVE,
            'applies_to_all_users' => false,
            'starts_at' => now(),
            'expires_at' => now()->addDays($prize['coupon_days']),
            'minimum_order_amount' => 0,
            'usage_limit' => 1,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'applies_to_addons' => false,
            'created_by_user_id' => null,
            'metadata' => ['source' => 'loyalty_champion', 'loyalty_champion_id' => $champion->getKey()],
        ]);

        app(AuditService::class)->sync($coupon, 'users', [$champion->user_id], false);

        return $coupon;
    }

    /** کد قرعه‌کشی در قرعه‌کشی باشگاه؛ اگر قرعه‌کشی‌ای فعال نباشد صفر برمی‌گردد. */
    private function issueTickets(LoyaltyChampion $champion, int $quantity): int
    {
        $campaign = DrawCampaign::query()->find((int) $this->settings->get('club_raffle_campaign_id', 0));
        if ($campaign === null || ! $campaign->acceptsTicketsAt(now())) {
            return 0;
        }

        try {
            app(IssueDrawTicketsAction::class)->execute(new IssueDrawTicketsDTO(
                campaign: $campaign,
                user: $champion->user,
                quantity: $quantity,
                idempotencyKey: 'champion-tickets:' . $champion->getKey(),
                source: $champion,
                metadata: ['source' => 'loyalty_champion'],
            ));
        } catch (Throwable $exception) {
            // سقف کد قرعه‌کشی یا بسته شدن قرعه‌کشی نباید بقیه‌ی جوایز را خراب کند.
            report($exception);

            return 0;
        }

        return $quantity;
    }
}
