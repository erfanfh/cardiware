<?php

namespace App\Application\Launcher\Actions\Security;

use App\Application\Launcher\Exceptions\DeviceRevokedException;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        [$privateKey, $publicKey] = $this->loadOrCreateSigningKey();
        $issuedAt = now()->utc();
        // Normally 7 days; 8..30 while emergency-offline mode is on.
        $licenceDays = app(\App\Application\Launcher\Support\LauncherEmergencyOffline::class)->licenceDays();
        $expiresAt = $issuedAt->copy()->addDays($licenceDays);

        // Device transfer while the previous device's licence is still valid. This used to
        // throw OfflineLicenseTransferCooldownException, on the assumption that only OFFLINE
        // play needs the licence -- but the licence is also the only carrier of the content
        // keys, so an ONLINE launch on the new (active) device failed too, for up to 7 days
        // ("اطلاعات لازم برای اجرای این بازی در دسترس نیست"). Issue the licence now, capped
        // so it cannot outlive the previous one: the total window in which two devices could
        // hold a valid licence is unchanged, and the old device is already deactivated for
        // any online use.
        if ($availableAt !== null && $availableAt->isFuture()) {
            $cappedExpiry = $availableAt->setTimezone('UTC');
            if ($cappedExpiry->lessThan($issuedAt->copy()->addHours(1))) {
                // Nearly over: a licence that expires within the hour would only bounce the
                // launcher straight back here; keep the normal window instead.
                $cappedExpiry = null;
            }
            if ($cappedExpiry !== null) {
                Log::info('launcher.offline_license.issued_during_transfer_cooldown', [
                    'user_id' => (int)$user->getKey(),
                    'device_id' => (int)$device->getKey(),
                    'capped_expires_at' => $cappedExpiry->toISOString(),
                ]);
                $expiresAt = $cappedExpiry;
            }
        }

        $usableAccesses = $user->purchaseAccesses()
            ->launcherUsable()
            ->with('productSaleItem.manifest:id,product_sale_item_id,content_key_id,content_key_value')
            ->orderBy('id')
            ->get(['id', 'product_id', 'product_sale_item_id', 'expires_at']);

        // محصولات بازشده با اشتراک ویژه: فقط تا پایان اشتراک VIP در مجوز
        // آفلاین معتبرند (launcher روی access_expires_at هر مورد فیلتر می‌کند).
        $hasBorrow = $user->purchaseAccesses()->withoutGlobalScopes()->vipBorrow()->exists();
        $vipExpiresAt = $hasBorrow ? $user->vipExpiresAt() : null;
        if ($vipExpiresAt !== null) {
            $ownedSaleItemIds = $usableAccesses->pluck('product_sale_item_id')->map(fn($id) => (int)$id)->all();
            $vipAccesses = $user->purchaseAccesses()
                ->vipBorrow()
                ->active()
                ->whereNotIn('product_sale_item_id', $ownedSaleItemIds)
                ->whereHas('productSaleItem', fn($query) => $query
                    ->where('launcher_enabled', true)
                    ->where('vip_access_enabled', true))
                ->with('productSaleItem.manifest:id,product_sale_item_id,content_key_id,content_key_value')
                ->orderBy('id')
                ->get(['id', 'product_id', 'product_sale_item_id', 'expires_at'])
                ->each(fn(PurchaseAccess $access) => $access->expires_at = $vipExpiresAt);

            $usableAccesses = $usableAccesses->concat($vipAccesses)->values();
        }

        $entitlements = $usableAccesses
            ->map(fn($access): array => [
                'purchase_access_id' => (int)$access->id,
                'product_id' => (int)$access->product_id,
                'product_sale_item_id' => (int)$access->product_sale_item_id,
                'access_expires_at' => $access->expires_at?->utc()->toISOString(),
            ])
            ->values()
            ->all();

        $contentKeys = $this->resolveContentKeys($usableAccesses);

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
            // Absent (never an empty object) when the user owns nothing protected --
            // matches OfflinePayload.ContentKeys being nullable on the launcher side.
            ...($contentKeys !== [] ? ['content_keys' => $contentKeys] : []),
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
            'max_offline_days' => $licenceDays,
        ];
        });
    }

    /**
     * Builds the content_key_id -> Base64(32-byte key) map for everything the user
     * currently owns and is entitled to use offline. Lives INSIDE the RS256-signed
     * payload (see execute() above) -- not the outer envelope -- so a tampered key can
     * never pass the launcher's signature verification.
     *
     * @param Collection<int, PurchaseAccess> $usableAccesses
     * @return array<string, string>
     */
    private function resolveContentKeys(Collection $usableAccesses): array
    {
        $contentKeys = [];

        foreach ($usableAccesses as $access) {
            $manifest = $access->productSaleItem?->manifest;

            if ($manifest === null
                || blank($manifest->content_key_id)
                || blank($manifest->content_key_value)) {
                continue;
            }

            $keyId = $manifest->content_key_id;

            if (array_key_exists($keyId, $contentKeys)
                && $contentKeys[$keyId] !== $manifest->content_key_value) {
                // The DB-level unique constraint on content_key_id should make this
                // unreachable; if it ever happens, log it loudly rather than silently
                // pick one of two different keys for the same id.
                Log::error('launcher.offline_license.content_key_id_collision', [
                    'content_key_id' => $keyId,
                    'purchase_access_id' => $access->id,
                ]);
                continue;
            }

            $contentKeys[$keyId] = $manifest->content_key_value;
        }

        return $contentKeys;
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
