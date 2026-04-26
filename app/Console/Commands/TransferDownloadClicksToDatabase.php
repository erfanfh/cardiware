<?php

namespace App\Console\Commands;

use App\Domain\Download\Models\DownloadEntry;
use App\Infrastructure\Support\Counters\BufferedCounter;
use Illuminate\Console\Command;

class TransferDownloadClicksToDatabase extends Command
{
    protected $signature = 'app:transfer-download-clicks-to-database {--limit=5000}';

    protected $description = 'Transfer buffered download clicks from Redis to database';

    public function handle(): int
    {
        $limit = (int)$this->option('limit');

        for ($i = 0; $i < $limit; $i++) {
            $dirty = BufferedCounter::popDirty('download_clicks');

            if (!$dirty) {
                break;
            }

            [$type, $id] = BufferedCounter::parseDirtyValue($dirty);

            if ($type !== 'DownloadEntry') {
                continue;
            }

            $count = BufferedCounter::pull(
                namespace: 'download_clicks',
                type: $type,
                id: $id,
            );

            if ($count <= 0) {
                continue;
            }

            DownloadEntry::query()
                ->whereKey($id)
                ->increment('download_click_count', $count);
        }

        return self::SUCCESS;
    }
}
