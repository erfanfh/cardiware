<?php

namespace App\Application\System\Jobs;

use App\Application\System\Backup\BackupConfigManager;
use App\Application\System\Backup\BackupEnvironment;
use App\Domain\System\Models\BackupRun;
use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Throwable;

final class RunSystemBackupJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 7200;

    public bool $failOnTimeout = true;

    public function __construct(public readonly int $backupRunId)
    {
        $this->onQueue('default');
    }

    public function handle(
        BackupConfigManager $configManager,
        BackupEnvironment $environment,
        BackupSettings $settings,
    ): void {
        $run = BackupRun::query()->findOrFail($this->backupRunId);
        $run->update(['status' => 'running', 'started_at' => now(), 'message' => null]);

        try {
            $configManager->apply();

            if (! $environment->isReady()) {
                throw new RuntimeException('Backup environment is incomplete: '.implode(', ', $environment->missing()));
            }

            $parameters = ['--disable-notifications' => true];
            $filesEnabled = (bool) $settings->value('files_enabled');
            $databaseEnabled = (bool) $settings->value('database_enabled');

            if ($run->type === 'database' || ! $filesEnabled) {
                $parameters['--only-db'] = true;
            } elseif (! $databaseEnabled) {
                $parameters['--only-files'] = true;
            }

            $exitCode = Artisan::call('backup:run', $parameters);
            $output = trim(Artisan::output());

            if ($exitCode !== 0) {
                throw new RuntimeException($output ?: "backup:run exited with code {$exitCode}.");
            }

            $run->update([
                'status' => 'succeeded',
                'finished_at' => now(),
                'message' => mb_substr($output ?: 'Backup completed.', -5000),
            ]);
        } catch (Throwable $exception) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'message' => mb_substr($exception->getMessage(), 0, 5000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        BackupRun::query()
            ->whereKey($this->backupRunId)
            ->whereIn('status', ['queued', 'running'])
            ->update([
                'status' => 'failed',
                'finished_at' => now(),
                'message' => mb_substr($exception?->getMessage() ?: 'Backup job failed.', 0, 5000),
            ]);
    }

    public function tags(): array
    {
        return ['backup', 'backup-run:'.$this->backupRunId];
    }
}
