<?php

namespace App\Application\System\Backup;

use App\Application\System\Jobs\RunSystemBackupJob;
use App\Domain\System\Models\BackupRun;
use Illuminate\Support\Facades\DB;

final class BackupRunDispatcher
{
    public function dispatch(string $type, string $trigger, ?int $userId = null): ?BackupRun
    {
        return DB::transaction(function () use ($type, $trigger, $userId): ?BackupRun {
            BackupRun::query()
                ->where('status', 'queued')
                ->where('created_at', '<', now()->subHours(12))
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'message' => 'The queued backup did not start within 12 hours.',
                ]);

            BackupRun::query()
                ->where('status', 'running')
                ->where('started_at', '<', now()->subHours(3))
                ->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'message' => 'The backup exceeded its maximum execution window.',
                ]);

            $alreadyActive = BackupRun::query()
                ->whereIn('status', ['queued', 'running'])
                ->lockForUpdate()
                ->exists();

            if ($alreadyActive) {
                return null;
            }

            $run = BackupRun::query()->create([
                'type' => $type,
                'trigger' => $trigger,
                'status' => 'queued',
                'requested_by_user_id' => $userId,
            ]);

            DB::afterCommit(static fn () => RunSystemBackupJob::dispatch($run->id));

            return $run;
        });
    }
}
