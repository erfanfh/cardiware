<?php

namespace App\Application\Launcher\Actions\Security;

use App\Application\Launcher\Exceptions\OfflineLicenseTransferCooldownException;
use App\Application\Launcher\Exceptions\DeviceRevokedException;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class IssueOfflineLicenseAction
{
    /** @return array<string, mixed> */
    public function execute(User $user, LauncherDevice $device): array
    {
        return DB::transaction(function () use ($user, $device): array {
        $user = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
        $device = LauncherDevice::query()->whereKey($device->getKey())->lockForUpdate()->firstOrFail();
        if (
            !$device->isActive()
            || (int)$device->device_slot !== (int)$user->active_device_slot
            || (int)$device->user_id !== (int)$user->getKey()
        ) {
            throw new DeviceRevokedException();
        }

        $previousExpiry = LauncherDevice::query()
            ->where('user_id', $user->getKey())
            ->where('id', '!=', $device->getKey())
            ->max('offline_license_expires_at');
        $availableAt = $previousExpiry === null ? null : CarbonImmutable::parse($previousExpiry);
        if ($availableAt !== null && $availableAt->isFuture()) {
            throw new OfflineLicenseTransferCooldownException($availableAt);
        }

        [$privateKey, $publicKey] = $this->loadOrCreateSigningKey();
        $issuedAt = now()->utc();
        $expiresAt = $issuedAt->copy()->addDays(
            min(7, max(1, (int)config('launcher_security.offline_license_days', 7)))
        );

        $entitlements = $user->purchaseAccesses()
            ->usable()
            ->orderBy('id')
            ->get(['id', 'product_id', 'product_sale_item_id', 'expires_at'])
            ->map(fn($access): array => [
                'purchase_access_id' => (int)$access->id,
                'product_id' => (int)$access->product_id,
                'product_sale_item_id' => (int)$access->product_sale_item_id,
                'access_expires_at' => $access->expires_at?->utc()->toISOString(),
            ])
            ->values()
            ->all();

        $payload = [
            'version' => 1,
            'license_id' => (string)Str::uuid(),
            'user_id' => (int)$user->getKey(),
            'device_public_key_fingerprint' => (string)$device->public_key_fingerprint,
            'device_slot' => (int)$device->device_slot,
            'issued_at' => $issuedAt->toISOString(),
            'not_before' => $issuedAt->copy()->subMinutes(5)->toISOString(),
            'expires_at' => $expiresAt->toISOString(),
            'entitlements' => $entitlements,
        ];

        $payloadJson = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        if (!openssl_sign($payloadJson, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('امضای مجوز آفلاین انجام نشد.');
        }

        $device->forceFill(['offline_license_expires_at' => $expiresAt])->save();

        return [
            'algorithm' => 'RS256',
            'key_id' => hash('sha256', $publicKey),
            'payload' => $this->base64UrlEncode($payloadJson),
            'signature' => $this->base64UrlEncode($signature),
            'public_key' => $publicKey,
            'expires_at' => $expiresAt->toISOString(),
            'max_offline_days' => min(7, max(1, (int)config('launcher_security.offline_license_days', 7))),
        ];
        });
    }

    /** @return array{0: string, 1: string} */
    private function loadOrCreateSigningKey(): array
    {
        $directory = storage_path('app/private/launcher');
        $privatePath = $directory . DIRECTORY_SEPARATOR . 'offline-license-private.pem';
        $publicPath = $directory . DIRECTORY_SEPARATOR . 'offline-license-public.pem';

        if (File::isFile($privatePath) && File::isFile($publicPath)) {
            return [File::get($privatePath), File::get($publicPath)];
        }

        File::ensureDirectoryExists($directory, 0700, true);
        $lock = fopen($directory . DIRECTORY_SEPARATOR . '.keygen.lock', 'c+');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new RuntimeException('قفل ساخت کلید مجوز آفلاین ایجاد نشد.');
        }

        try {
            if (File::isFile($privatePath) && File::isFile($publicPath)) {
                return [File::get($privatePath), File::get($publicPath)];
            }

            $resource = openssl_pkey_new([
                'private_key_bits' => 3072,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]);
            if ($resource === false || !openssl_pkey_export($resource, $privateKey)) {
                throw new RuntimeException('ساخت کلید خصوصی مجوز آفلاین ناموفق بود.');
            }

            $details = openssl_pkey_get_details($resource);
            $publicKey = $details['key'] ?? null;
            if (!is_string($publicKey)) {
                throw new RuntimeException('ساخت کلید عمومی مجوز آفلاین ناموفق بود.');
            }

            File::put($privatePath, $privateKey, true);
            @chmod($privatePath, 0600);
            File::put($publicPath, $publicKey, true);
            @chmod($publicPath, 0644);

            return [$privateKey, $publicKey];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
