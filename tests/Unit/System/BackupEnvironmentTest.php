<?php

namespace Tests\Unit\System;

use App\Application\System\Backup\BackupEnvironment;
use Tests\TestCase;

final class BackupEnvironmentTest extends TestCase
{
    public function test_missing_secrets_make_environment_not_ready(): void
    {
        config([
            'filesystems.disks.parspack_backup.key' => null,
            'filesystems.disks.parspack_backup.secret' => null,
            'backup.backup.password' => null,
        ]);

        $environment = new BackupEnvironment;

        self::assertFalse($environment->isReady());
        self::assertContains('access_key', $environment->missing());
        self::assertContains('secret_key', $environment->missing());
        self::assertContains('archive_password', $environment->missing());
    }

    public function test_short_archive_password_is_rejected(): void
    {
        config(['backup.backup.password' => 'too-short']);

        self::assertFalse((new BackupEnvironment)->checks()['archive_password']);
    }
}
