<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

final class BackupSettings extends BaseSettings
{
    protected string $group = 'backup';

    protected array $allowedKeys = [
        'enabled',
        'files_enabled',
        'database_enabled',
        'wordpress_database_enabled',
        'full_interval_hours',
        'database_interval_hours',
        'keep_all_days',
        'keep_daily_days',
        'keep_weekly_weeks',
        'keep_monthly_months',
        'keep_yearly_years',
        'storage_limit_gb',
    ];

    protected array $casts = [
        'enabled' => BooleanCast::class,
        'files_enabled' => BooleanCast::class,
        'database_enabled' => BooleanCast::class,
        'wordpress_database_enabled' => BooleanCast::class,
        'full_interval_hours' => IntegerCast::class,
        'database_interval_hours' => IntegerCast::class,
        'keep_all_days' => IntegerCast::class,
        'keep_daily_days' => IntegerCast::class,
        'keep_weekly_weeks' => IntegerCast::class,
        'keep_monthly_months' => IntegerCast::class,
        'keep_yearly_years' => IntegerCast::class,
        'storage_limit_gb' => IntegerCast::class,
    ];

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'files_enabled' => true,
            'database_enabled' => true,
            'wordpress_database_enabled' => false,
            'full_interval_hours' => 24,
            'database_interval_hours' => 6,
            'keep_all_days' => 7,
            'keep_daily_days' => 30,
            'keep_weekly_weeks' => 12,
            'keep_monthly_months' => 12,
            'keep_yearly_years' => 2,
            'storage_limit_gb' => 140,
        ];
    }

    public function value(string $key): mixed
    {
        return $this->get($key, self::defaults()[$key] ?? null);
    }
}
