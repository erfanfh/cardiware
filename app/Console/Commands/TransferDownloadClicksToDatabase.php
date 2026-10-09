<?php

namespace App\Console\Commands;

use App\Infrastructure\Support\Counters\DownloadClickCounter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TransferDownloadClicksToDatabase extends Command
{
    protected $signature = 'app:transfer-download-clicks-to-database {--limit=5000}';

    protected $description = 'Transfer buffered download clicks from Redis to database';

    public function handle(DownloadClickCounter $counter): int
    {
        $limit = max(0, (int) $this->option('limit'));
        if ($limit === 0) {
            return self::SUCCESS;
        }

        $lock = Cache::store('download_counters')->lock('downloads:flush-clicks', 600);
        $acquired = false;

        try {
            // Share this lock with the legacy command and other scheduler hosts.
            if (! ($acquired = $lock->get())) {
                return self::SUCCESS;
            }

            foreach ($counter->pendingEntryIds($limit) as $entryId) {
                if (! $lock->refresh()) {
                    $this->error('Download click transfer lock was lost.');

                    return self::FAILURE;
                }

                $count = $counter->pendingCount($entryId);

                if ($count > 0) {
                    DB::table('download_entries')
                        ->where('id', $entryId)
                        ->increment('download_click_count', $count);
                }

                // Keep the buffer/dirty marker until SQL succeeds. Missing rows are discarded.
                $counter->acknowledge($entryId, $count);
            }
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Download click transfer failed; unacknowledged counts remain buffered.');

            return self::FAILURE;
        } finally {
            if ($acquired) {
                try {
                    $lock->release();
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }

        return self::SUCCESS;
    }
}
