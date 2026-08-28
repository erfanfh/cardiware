<?php

namespace App\Application\System\Backup;

use Spatie\Backup\BackupServiceProvider;
use Symfony\Component\Process\ExecutableFinder;

final class BackupEnvironment
{
    /** @return array<string, bool> */
    public function checks(): array
    {
        return [
            'backup_package' => class_exists(BackupServiceProvider::class),
            'access_key' => filled(config('filesystems.disks.parspack_backup.key')),
            'secret_key' => filled(config('filesystems.disks.parspack_backup.secret')),
            'bucket' => filled(config('filesystems.disks.parspack_backup.bucket')),
            'endpoint' => filled(config('filesystems.disks.parspack_backup.endpoint')),
            'region' => filled(config('filesystems.disks.parspack_backup.region')),
            'archive_password' => mb_strlen((string) config('backup.backup.password')) >= 32,
            'zip_extension' => extension_loaded('zip'),
            'database_dump_binary' => (new ExecutableFinder)->find('mysqldump') !== null,
        ];
    }

    public function isReady(): bool
    {
        return ! in_array(false, $this->checks(), true);
    }

    /** @return list<string> */
    public function missing(): array
    {
        return array_keys(array_filter($this->checks(), static fn (bool $ready): bool => ! $ready));
    }
}
