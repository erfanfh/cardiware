<?php

namespace App\Application\Launcher\Actions\Security;

use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use Illuminate\Support\Facades\DB;

final class ResetLauncherDeviceSecurityAction
{
    public function execute(
        User $targetUser,
        string $reason,
        ?User $adminUser = null,
        string $source = 'admin',
        ?string $ipAddress = null,
    ): int {
        return DB::transaction(function () use (
            $targetUser,
            $reason,
            $adminUser,
            $source,
            $ipAddress,
        ): int {
            $user = User::query()
                ->whereKey($targetUser->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $devices = $user->launcherDevices()
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            $snapshot = [
                'active_device_slot' => $user->active_device_slot,
                'device_transfer_used' => (bool)$user->device_transfer_used,
                'first_activated_at' => $user->first_activated_at?->toISOString(),
                'transferred_at' => $user->transferred_at?->toISOString(),
                'devices' => $devices->map(fn($device): array => [
                    'id' => $device->device_id,
                    'name' => $device->name,
                    'slot' => $device->device_slot,
                    'public_key_fingerprint' => $device->public_key_fingerprint,
                    'last_ip' => $device->last_ip,
                    'last_seen_at' => $device->last_seen_at?->toISOString(),
                    'revoked_at' => $device->revoked_at?->toISOString(),
                    'offline_license_expires_at' => $device->offline_license_expires_at?->toISOString(),
                ])->all(),
            ];

            foreach ($devices as $device) {
                $device->tokens()->delete();
                $device->forceFill(['revoked_at' => now()])->save();
            }

            $user->tokens()->where('name', 'like', 'MW Launcher%')->delete();
            $invalidatedAt = now();
            PurchaseAccessDownload::query()
                ->where('user_id', $user->getKey())
                ->whereNull('invalidated_at')
                ->update([
                    'invalidated_at' => $invalidatedAt,
                    'session_expires_at' => $invalidatedAt,
                ]);
            LauncherDownloadSession::query()
                ->whereHas('access', fn($query) => $query->where('user_id', $user->getKey()))
                ->whereNull('invalidated_at')
                ->update([
                    'invalidated_at' => $invalidatedAt,
                    'session_expires_at' => $invalidatedAt,
                ]);
            $user->forceFill([
                'first_device_public_key' => null,
                'first_device_fingerprint' => null,
                'first_device_hardware_fingerprint' => null,
                'second_device_public_key' => null,
                'second_device_fingerprint' => null,
                'second_device_hardware_fingerprint' => null,
                'active_device_slot' => null,
                'device_transfer_used' => false,
                'first_activated_at' => null,
                'transferred_at' => null,
            ])->save();

            DB::table('launcher_device_reset_logs')->insert([
                'target_user_id' => $user->getKey(),
                'admin_user_id' => $adminUser?->getKey(),
                'source' => $source,
                'reason' => trim($reason),
                'ip_address' => $ipAddress,
                'device_snapshot' => json_encode(
                    $snapshot,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                ),
                'created_at' => now(),
            ]);

            return $devices->count();
        });
    }
}
