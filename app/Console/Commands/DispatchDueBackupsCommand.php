<?php

namespace App\Console\Commands;

use App\Application\System\Backup\BackupEnvironment;
use App\Application\System\Backup\BackupRunDispatcher;
use App\Domain\System\Models\BackupRun;
use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use Illuminate\Console\Command;

final class DispatchDueBackupsCommand extends Command
{
    protected $signature = 'backups:dispatch-due';

    protected $description = 'Queue the database or full backup that is due according to admin settings.';

    public function handle(
        BackupSettings $settings,
        BackupEnvironment $environment,
        BackupRunDispatcher $dispatcher,
    ): int {
        if (! $settings->value('enabled')) {
            return self::SUCCESS;
        }

        if (! $environment->isReady()) {
            $this->error('Backup environment is incomplete: '.implode(', ', $environment->missing()));

            return self::FAILURE;
        }

        if (BackupRun::query()->whereIn('status', ['queued', 'running'])->exists()) {
            return self::SUCCESS;
        }

        if ($settings->value('files_enabled') && $this->isDue('full', (int) $settings->value('full_interval_hours'))) {
            $dispatcher->dispatch('full', 'schedule');

            return self::SUCCESS;
        }

        if ($settings->value('database_enabled') && $this->databaseIsDue((int) $settings->value('database_interval_hours'))) {
            $dispatcher->dispatch('database', 'schedule');
        }

        return self::SUCCESS;
    }

    private function isDue(string $type, int $hours): bool
    {
        $last = BackupRun::query()
            ->where('type', $type)
            ->where('status', 'succeeded')
            ->latest('finished_at')
            ->value('finished_at');

        return ! $last || now()->diffInHours($last, absolute: true) >= $hours;
    }

    private function databaseIsDue(int $hours): bool
    {
        $last = BackupRun::query()
            ->whereIn('type', ['database', 'full'])
            ->where('status', 'succeeded')
            ->latest('finished_at')
            ->value('finished_at');

        return ! $last || now()->diffInHours($last, absolute: true) >= $hours;
    }
}
