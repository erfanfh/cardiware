<?php

namespace App\Application\Launcher\Actions\Security;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

final class ChangeLauncherAccountSuspensionAction
{
    public function suspend(
        User $targetUser,
        string $reason,
        ?int $durationDays,
        ?User $adminUser,
        ?string $ipAddress,
    ): void {
        DB::transaction(function () use ($targetUser, $reason, $durationDays, $adminUser, $ipAddress): void {
            $user = User::query()->whereKey($targetUser->getKey())->lockForUpdate()->firstOrFail();
            $until = $durationDays === null ? null : now()->addDays($durationDays);

            $user->forceFill([
                'launcher_suspended_at' => now(),
                'launcher_suspended_until' => $until,
                'launcher_suspension_reason' => trim($reason),
                'launcher_suspended_by' => $adminUser?->getKey(),
            ])->save();

            // محرومیت حساب با Reset دستگاه متفاوت است: اسلات‌ها و فرصت انتقال حفظ می‌شوند.
            $user->tokens()->where('name', 'like', 'MW Launcher%')->delete();

            $this->log($user, $adminUser, 'suspend', $reason, $until, $ipAddress);
        });
    }

    public function restore(
        User $targetUser,
        string $reason,
        ?User $adminUser,
        ?string $ipAddress,
    ): void {
        DB::transaction(function () use ($targetUser, $reason, $adminUser, $ipAddress): void {
            $user = User::query()->whereKey($targetUser->getKey())->lockForUpdate()->firstOrFail();
            $user->forceFill([
                'launcher_suspended_at' => null,
                'launcher_suspended_until' => null,
                'launcher_suspension_reason' => null,
                'launcher_suspended_by' => null,
            ])->save();

            $this->log($user, $adminUser, 'restore', $reason, null, $ipAddress);
        });
    }

    private function log(
        User $targetUser,
        ?User $adminUser,
        string $action,
        ?string $reason,
        mixed $until,
        ?string $ipAddress,
    ): void {
        DB::table('launcher_access_suspension_logs')->insert([
            'target_user_id' => $targetUser->getKey(),
            'admin_user_id' => $adminUser?->getKey(),
            'action' => $action,
            'reason' => blank($reason) ? null : trim((string)$reason),
            'suspended_until' => $until,
            'ip_address' => $ipAddress,
            'created_at' => now(),
        ]);
    }
}
