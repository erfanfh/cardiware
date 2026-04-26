<?php

namespace App\Console\Commands;

use App\Domain\Download\Models\DownloadEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FlushDownloadEntryClicksCommand extends Command
{
    protected $signature = 'downloads:flush-clicks {--limit=1000}';

    protected $description = 'Flush dirty download click counters from Redis to database';

    public function handle(): int
    {
        $limit = (int)$this->option('limit');

        for ($i = 0; $i < $limit; $i++) {
            $entryId = Redis::spop('download_entry_clicks:dirty');

            if (!$entryId) {
                break;
            }

            $key = "download_entry_clicks:{$entryId}";

            $count = (int)Redis::getdel($key);

            if ($count <= 0) {
                continue;
            }

            DownloadEntry::query()
                ->whereKey($entryId)
                ->increment('download_click_count', $count);
        }

        return self::SUCCESS;
    }
}
