<?php

namespace App\Console\Commands;

use App\Application\Launcher\Actions\Security\ResetLauncherDeviceSecurityAction;
use App\Domain\Identity\Models\User;
use Illuminate\Console\Command;

final class ResetLauncherDeviceSecurityCommand extends Command
{
    protected $signature = 'launcher:reset-device {user : User ID, email or mobile} {--force : Skip confirmation}';
    protected $description = 'Support-only reset of launcher device activation and transfer history';

    public function handle(ResetLauncherDeviceSecurityAction $resetDeviceSecurity): int
    {
        $identifier = (string)$this->argument('user');
        $user = User::query()
            ->whereKey(is_numeric($identifier) ? (int)$identifier : 0)
            ->orWhere('email', $identifier)
            ->orWhere('mobile', $identifier)
            ->first();

        if (!$user instanceof User) {
            $this->error('User not found.');
            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm(
            "Reset all launcher device security for user #{$user->getKey()}?"
        )) {
            return self::SUCCESS;
        }

        $count = $resetDeviceSecurity->execute(
            targetUser: $user,
            reason: 'Manual support reset from Artisan by ' . get_current_user(),
            source: 'artisan',
        );

        $this->info("Launcher device activation was reset ({$count} device records revoked). The next verified login becomes device one.");
        return self::SUCCESS;
    }
}
