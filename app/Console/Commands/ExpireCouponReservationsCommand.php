<?php

namespace App\Console\Commands;

use App\Application\Promotion\Actions\Coupon\ExpireCouponReservationsAction;
use Illuminate\Console\Command;

class ExpireCouponReservationsCommand extends Command
{
    protected $signature = 'coupons:expire-reservations';

    protected $description = 'Expire old active coupon reservations.';

    public function handle(ExpireCouponReservationsAction $action): int
    {
        $count = $action->execute();

        $this->info("Expired {$count} coupon reservation(s).");

        return self::SUCCESS;
    }
}
