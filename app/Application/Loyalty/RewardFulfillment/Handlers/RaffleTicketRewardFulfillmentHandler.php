<?php

namespace App\Application\Loyalty\RewardFulfillment\Handlers;

use App\Application\Loyalty\RewardFulfillment\Contracts\RewardFulfillmentHandler;
use App\Application\Raffle\Actions\Ticket\IssueDrawTicketsAction;
use App\Application\Raffle\DTOs\IssueDrawTicketsDTO;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\RewardTypeEnum;
use App\Domain\Loyalty\Models\RewardOffer;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Models\DrawCampaign;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class RaffleTicketRewardFulfillmentHandler implements RewardFulfillmentHandler
{
    public function __construct(
        private readonly IssueDrawTicketsAction $issueDrawTickets,
    ) {
    }

    public function type(): RewardTypeEnum
    {
        return RewardTypeEnum::RAFFLE_TICKET;
    }

    public function fulfill(
        RewardOffer $offer,
        RewardRedemption $redemption,
        User $user,
    ): Model {
        $campaign = $offer->target;

        if (!$campaign instanceof DrawCampaign) {
            throw new InvalidArgumentException(
                'Raffle ticket reward must target a valid draw campaign.'
            );
        }

        $quantity = (int) ($offer->config['ticket_quantity'] ?? 0);

        if ($quantity <= 0) {
            throw new InvalidArgumentException(
                'Raffle ticket reward quantity must be greater than zero.'
            );
        }

        return $this->issueDrawTickets->execute(new IssueDrawTicketsDTO(
            campaign: $campaign,
            user: $user,
            quantity: $quantity,
            idempotencyKey: 'loyalty:reward-redemption:' . $redemption->getKey() . ':raffle-tickets',
            source: $redemption,
            issuedBy: null,
            occurredAt: now()->toImmutable(),
            metadata: [
                'origin' => 'loyalty_reward',
                'reward_offer_id' => $offer->getKey(),
                'reward_redemption_id' => $redemption->getKey(),
                'points_spent' => (int) $redemption->points_spent,
                'invalidation_mode' => 'grant',
            ],
        ));
    }
}
