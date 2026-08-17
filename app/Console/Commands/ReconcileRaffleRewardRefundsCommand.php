<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Actions\Reward\RefundRaffleTicketRewardRedemptionAction;
use App\Domain\Loyalty\Enums\RewardRedemptionStatusEnum;
use App\Domain\Loyalty\Models\RewardRedemption;
use App\Domain\Raffle\Models\TicketGrant;
use Illuminate\Console\Command;
use Throwable;

class ReconcileRaffleRewardRefundsCommand extends Command
{
    protected $signature = 'loyalty:reconcile-raffle-ticket-refunds {--limit=1000}';
    protected $description = 'Refund completed loyalty raffle-ticket redemptions whose whole ticket grant has been invalidated.';

    /** @throws Throwable */
    public function handle(RefundRaffleTicketRewardRedemptionAction $refund): int
    {
        $grantMorph = (new TicketGrant())->getMorphClass();
        $limit = max(1, min(10000, (int) $this->option('limit')));
        $processed = 0;

        $redemptions = RewardRedemption::query()
            ->where('status', RewardRedemptionStatusEnum::COMPLETED->value)
            ->where('fulfillment_type', $grantMorph)
            ->whereNotNull('fulfillment_id')
            ->whereIn(
                'fulfillment_id',
                TicketGrant::query()
                    ->select('id')
                    ->whereNotNull('invalidated_at')
            )
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($redemptions as $redemption) {
            $refund->execute(
                redemption: $redemption,
                reason: 'بازگشت امتیاز توسط فرآیند تطبیق خودکار ابطال بلیط قرعه کشی',
            );
            $processed++;
        }

        $this->info("Reconciled {$processed} raffle reward redemption(s).");

        return self::SUCCESS;
    }
}
