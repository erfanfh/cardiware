<?php

namespace App\Console\Commands;

use App\Application\Commerce\Actions\Cart\CleanupStaleActiveCartsAction;
use Illuminate\Console\Command;

class CleanupStaleActiveCartsCommand extends Command
{
    protected $signature = 'carts:cleanup-stale';

    protected $description = 'Delete stale active carts using separate retention periods for guests and registered users.';

    public function handle(CleanupStaleActiveCartsAction $cleanupStaleActiveCartsAction): int
    {
        $guestRetentionDays = $cleanupStaleActiveCartsAction->guestRetentionDays();
        $userRetentionMonths = $cleanupStaleActiveCartsAction->userRetentionMonths();

        $deleted = $cleanupStaleActiveCartsAction->execute(
            $guestRetentionDays,
            $userRetentionMonths,
        );

        $this->info(
            "Deleted {$deleted['total']} stale active cart(s): "
            . "{$deleted['guest']} guest cart(s) older than {$guestRetentionDays} day(s), "
            . "{$deleted['user']} registered-user cart(s) older than {$userRetentionMonths} month(s).",
        );

        return self::SUCCESS;
    }
}
