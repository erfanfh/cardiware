<?php

namespace App\Console\Commands;

use App\Application\Loyalty\Actions\ExpireInactivePointsAction;
use Illuminate\Console\Command;

class ExpireInactiveLoyaltyPointsCommand extends Command
{
    protected $signature = 'loyalty:expire-inactive-points';

    protected $description = 'امتیاز کاربرانی که مدت‌ها فعالیت امتیازی نداشته‌اند را بر اساس تنظیمات باشگاه منقضی می‌کند';

    public function handle(ExpireInactivePointsAction $action): int
    {
        $entries = $action->execute();

        $total = array_sum(array_map(fn ($entry) => (int) $entry->amount, $entries));

        $this->info(sprintf(
            'انقضای %d حساب با مجموع %s امتیاز انجام شد.',
            count($entries),
            number_format($total),
        ));

        return self::SUCCESS;
    }
}
