<?php

namespace App\Console\Commands;

use App\Application\Support\Actions\CloseInactiveAnsweredTicketsAction;
use Illuminate\Console\Command;

class AutoCloseInactiveTicketsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tickets:auto-close-inactive';

    /**
     * The console command description.
     */
    protected $description = 'Close answered tickets with no messages during the last seven days';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $closedTicketsCount = app(CloseInactiveAnsweredTicketsAction::class)->execute();

        $this->info(
            "{$closedTicketsCount} inactive answered ticket(s) closed."
        );

        return self::SUCCESS;
    }
}
