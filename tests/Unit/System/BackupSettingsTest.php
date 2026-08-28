<?php

namespace Tests\Unit\System;

use App\Infrastructure\Services\Setting\Groups\BackupSettings;
use Tests\TestCase;

final class BackupSettingsTest extends TestCase
{
    public function test_every_backup_default_is_an_allowed_setting(): void
    {
        $settings = new BackupSettings;

        foreach (array_keys(BackupSettings::defaults()) as $key) {
            self::assertContains($key, $settings->getAllowedKeys());
        }
    }

    public function test_safe_capacity_and_schedule_defaults_are_used(): void
    {
        self::assertSame(140, BackupSettings::defaults()['storage_limit_gb']);
        self::assertSame(24, BackupSettings::defaults()['full_interval_hours']);
        self::assertSame(6, BackupSettings::defaults()['database_interval_hours']);
        self::assertFalse(BackupSettings::defaults()['enabled']);
    }
}
