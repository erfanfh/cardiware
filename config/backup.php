<?php

use Spatie\Backup\Notifications\Notifiable;
use Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;

return [
    'backup' => [
        'name' => env('APP_NAME', 'moddingway'),
        'source' => [
            'files' => [
                'include' => [base_path()],
                'exclude' => [
                    base_path('.git'),
                    base_path('node_modules'),
                    base_path('vendor'),
                    public_path('build'),
                    public_path('hot'),
                    storage_path('app/backup-temp'),
                    storage_path('debugbar'),
                    storage_path('framework'),
                    storage_path('logs'),
                    storage_path('pail'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => true,
                'relative_path' => base_path(),
            ],
            'databases' => [env('DB_CONNECTION', 'mysql')],
        ],
        'database_dump_compressor' => null,
        'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',
        'database_dump_filename_base' => 'connection',
        'database_dump_file_extension' => '',
        'destination' => [
            'compression_method' => ZipArchive::CM_DEFAULT,
            'compression_level' => 6,
            'filename_prefix' => '',
            'disks' => ['parspack_backup'],
            'continue_on_failure' => false,
        ],
        'temporary_directory' => storage_path('app/backup-temp'),
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),
        'encryption' => 'aes256',
        'verify_backup' => true,
        'tries' => 2,
        'retry_delay' => 60,
    ],

    // Operational failures are recorded in backup_runs and shown in the admin panel.
    'notifications' => [
        'notifications' => [],
        'notifiable' => Notifiable::class,
        'mail' => [
            'to' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'ModdingWay'),
            ],
        ],
        'slack' => ['webhook_url' => '', 'channel' => null, 'username' => null, 'icon' => null],
        'discord' => ['webhook_url' => '', 'username' => '', 'avatar_url' => ''],
        'webhook' => ['url' => ''],
    ],

    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'moddingway'),
            'disks' => ['parspack_backup'],
            'health_checks' => [
                MaximumAgeInDays::class => 2,
                MaximumStorageInMegabytes::class => 140 * 1024,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 30,
            'keep_weekly_backups_for_weeks' => 12,
            'keep_monthly_backups_for_months' => 12,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 140 * 1024,
        ],
        'tries' => 2,
        'retry_delay' => 60,
    ],
];
