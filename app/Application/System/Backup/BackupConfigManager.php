<?php

namespace App\Application\System\Backup;

use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

final class BackupConfigManager
{
    public function __construct(private readonly BackupSettings $settings)
    {
    }

    public function apply(): void
    {
        $storageLimitMb = max(1, (int)$this->settings->value('storage_limit_gb')) * 1024;
        $databases = [config('database.default')];

        config([
            'backup.backup.source.databases' => array_values(array_unique($databases)),
            'backup.cleanup.default_strategy.keep_all_backups_for_days' => (int)$this->settings->value('keep_all_days'),
            'backup.cleanup.default_strategy.keep_daily_backups_for_days' => (int)$this->settings->value('keep_daily_days'),
            'backup.cleanup.default_strategy.keep_weekly_backups_for_weeks' => (int)$this->settings->value('keep_weekly_weeks'),
            'backup.cleanup.default_strategy.keep_monthly_backups_for_months' => (int)$this->settings->value('keep_monthly_months'),
            'backup.cleanup.default_strategy.keep_yearly_backups_for_years' => (int)$this->settings->value('keep_yearly_years'),
            'backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than' => $storageLimitMb,
            'backup.monitor_backups.0.health_checks' => [
                MaximumAgeInDays::class => max(2, (int)ceil((int)$this->settings->value('full_interval_hours') / 24) + 1),
                MaximumStorageInMegabytes::class => $storageLimitMb,
            ],
        ]);
    }
}
