<?php

namespace App\Application\Loyalty\Actions;

use App\Application\Loyalty\RewardFulfillment\DTOs\RewardOfferAvailabilityDTO;
use App\Application\Raffle\Actions\CheckRaffleEligibilityAction;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;

class EvaluateRewardOfferAvailabilityAction
{
    public function __construct(private readonly CheckRaffleEligibilityAction $checkEligibility) {}

    public function execute(RewardOffer $offer, User $user): RewardOfferAvailabilityDTO
    {
        if (!$offer->isCurrentlyActive()) {
            return RewardOfferAvailabilityDTO::denied('این پاداش در حال حاضر فعال نیست.');
        }

        if ($offer->hasReachedGlobalLimit()) {
            return RewardOfferAvailabilityDTO::denied('ظرفیت این پاداش تکمیل شده است.');
        }

        $userRedemptions = $offer->redemptions()
            ->where('user_id', $user->getKey())
            ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
            ->count();

        if (
            $offer->redemption_limit_per_user !== null
            && $userRedemptions >= (int) $offer->redemption_limit_per_user
        ) {
            return RewardOfferAvailabilityDTO::denied(
                'سقف دریافت این پاداش برای حساب شما تکمیل شده است.',
                ['user_redemptions' => $userRedemptions],
            );
        }

        $balance = (int) (PointAccount::query()
            ->where('user_id', $user->getKey())
            ->value('balance') ?? 0);

        if ($balance < (int) $offer->points_cost) {
            return RewardOfferAvailabilityDTO::denied(
                'امتیاز شما برای دریافت این پاداش کافی نیست.',
                [
                    'balance' => max(0, $balance),
                    'missing_points' => max(0, (int) $offer->points_cost - max(0, $balance)),
                ],
            );
        }

        if ($offer->type !== RewardTypeEnum::RAFFLE_TICKET) {
            return RewardOfferAvailabilityDTO::allowed([
                'balance' => max(0, $balance),
                'user_redemptions' => $userRedemptions,
            ]);
        }

        $offer->loadMissing('target');
        $campaign = $offer->target;

        if (!$campaign instanceof DrawCampaign) {
            return RewardOfferAvailabilityDTO::denied('قرعه کشی متصل به این پاداش معتبر نیست.');
        }

        $quantity = (int) ($offer->config['ticket_quantity'] ?? 0);

        if ($quantity <= 0) {
            return RewardOfferAvailabilityDTO::denied('تعداد بلیط این پاداش معتبر نیست.');
        }

        if (!$campaign->acceptsTicketsAt()) {
            $reason = match ($campaign->status->value) {
                'draft', 'scheduled' => 'دریافت بلیط این قرعه کشی هنوز شروع نشده است.',
                'closed', 'drawing', 'completed' => 'مهلت دریافت بلیط این قرعه کشی پایان یافته است.',
                'cancelled' => 'این قرعه کشی لغو شده است.',
                default => 'این قرعه کشی در حال حاضر بلیط جدید نمی پذیرد.',
            };

            return RewardOfferAvailabilityDTO::denied($reason, [
                'campaign' => $campaign,
                'ticket_quantity' => $quantity,
            ]);
        }

        if (!$this->checkEligibility->execute($campaign, $user)['eligible']) {
            return RewardOfferAvailabilityDTO::denied('شرایط شرکت در این قرعه‌کشی را ندارید.', [
                'campaign' => $campaign,
                'ticket_quantity' => $quantity,
            ]);
        }

        $remainingCapacity = $campaign->remainingTicketCapacity();
        if ($remainingCapacity !== null && $remainingCapacity < $quantity) {
            return RewardOfferAvailabilityDTO::denied(
                'ظرفیت باقی مانده قرعه کشی برای این پاداش کافی نیست.',
                [
                    'campaign' => $campaign,
                    'ticket_quantity' => $quantity,
                    'remaining_capacity' => $remainingCapacity,
                ],
            );
        }

        $activeUserTickets = DrawTicket::query()
            ->where('campaign_id', $campaign->getKey())
            ->where('user_id', $user->getKey())
            ->where('status', DrawTicketStatusEnum::ACTIVE->value)
            ->count();

        if (
            $campaign->ticket_limit_per_user !== null
            && ($activeUserTickets + $quantity) > (int) $campaign->ticket_limit_per_user
        ) {
            return RewardOfferAvailabilityDTO::denied(
                'با دریافت این پاداش از سقف مجاز بلیط شما در این قرعه کشی عبور می شود.',
                [
                    'campaign' => $campaign,
                    'ticket_quantity' => $quantity,
                    'active_user_tickets' => $activeUserTickets,
                ],
            );
        }

        return RewardOfferAvailabilityDTO::allowed([
            'balance' => max(0, $balance),
            'user_redemptions' => $userRedemptions,
            'campaign' => $campaign,
            'ticket_quantity' => $quantity,
            'active_user_tickets' => $activeUserTickets,
            'remaining_capacity' => $remainingCapacity,
        ]);
    }
}
