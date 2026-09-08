<?php

namespace App\Console\Commands;

use App\Application\Support\Actions\CleanupExpiredTicketAttachmentsAction;
use Illuminate\Console\Command;

class CleanupExpiredTicketAttachmentsCommand extends Command
{
    protected $signature = 'tickets:cleanup-images';

    protected $description = 'Delete expired images uploaded by ticket users';

    public function handle(CleanupExpiredTicketAttachmentsAction $action): int
    {
        $deletedCount = $action->execute();

        $this->info("{$deletedCount} expired ticket image(s) deleted.");

        return self::SUCCESS;
    }
}
