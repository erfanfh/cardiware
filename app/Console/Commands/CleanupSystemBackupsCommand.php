<?php

namespace App\Console\Commands;

use App\Application\System\Backup\BackupConfigManager;
use App\Application\System\Backup\BackupEnvironment;
use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class CleanupSystemBackupsCommand extends Command
{
    protected $signature = 'backups:cleanup';

    protected $description = 'Clean old backups using the retention policy configured in admin.';

    public function handle(
        BackupSettings $settings,
        BackupEnvironment $environment,
        BackupConfigManager $configManager,
    ): int {
        if (! $settings->value('enabled')) {
            return self::SUCCESS;
        }

        if (! $environment->isReady()) {
            $this->error('Backup environment is incomplete.');

            return self::FAILURE;
        }

        $configManager->apply();
        $exitCode = Artisan::call('backup:clean', ['--disable-notifications' => true]);
        $this->output->write(Artisan::output());

        return $exitCode;
    }
}
