<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlushDownloadEntryClicksCommand extends Command
{
    protected $signature = 'downloads:flush-clicks {--limit=1000}';

    protected $description = 'Flush dirty download click counters from Redis to database';

    public function handle(): int
    {
        return $this->call('app:transfer-download-clicks-to-database', [
            '--limit' => $this->option('limit'),
        ]);
    }
}
