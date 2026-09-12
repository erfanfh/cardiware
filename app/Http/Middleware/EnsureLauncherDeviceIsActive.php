<?php

namespace App\Http\Middleware;

use App\Application\Launcher\Exceptions\DeviceRevokedException;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Domain\Launcher\Models\LauncherDeviceNetworkSwitch;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureLauncherDeviceIsActive
{
    /** How stale last_seen_at may get before a request refreshes it (see handle()). */
    private const LAST_SEEN_WRITE_INTERVAL_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token === null || !$token->can('windows-app')) {
            throw new DeviceRevokedException();
        }

        $deviceId = $token->getAttribute('launcher_device_id');
        // The device row is read on every launcher request. Cached briefly and flushed by
        // LauncherCacheObserver on any update to the row (revocation, slot change), so a
        // revoke still takes effect on the very next request.
        $device = $deviceId
            ? Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_DEVICES, CacheTag::launcherDevice($deviceId)])
                ->remember(
                    CacheKey::launcherDevice($deviceId),
                    now()->addMinutes(2),
                    static fn(): ?LauncherDevice => LauncherDevice::query()->whereKey($deviceId)->first(),
                )
            : null;
        if ($device instanceof LauncherDevice && (int) $device->user_id !== (int) $request->user()->getKey()) {
            $device = null;
        }

        if (!$device instanceof LauncherDevice || !$device->isActive()) {
            $token->delete();
            throw new DeviceRevokedException();
        }

        $activeSlot = (int)($request->user()->active_device_slot ?: 0);
        if ($activeSlot === 0 || (int)$device->device_slot !== $activeSlot) {
            $token->delete();
            throw new DeviceRevokedException();
        }

        $activePublicKey = $activeSlot === 2
            ? $request->user()->second_device_public_key
            : $request->user()->first_device_public_key;
        $decodedKey = base64_decode((string)$activePublicKey, true);
        if (
            $decodedKey === false
            || !hash_equals(hash('sha256', $decodedKey), (string)$device->public_key_fingerprint)
        ) {
            $token->delete();
            throw new DeviceRevokedException();
        }

        $this->recordNetworkSwitch($device, $request->ip());

        // This ran an UPDATE on launcher_devices for every single launcher request (with a
        // few hundred launchers open: hundreds of row-locking writes a minute for a value
        // nobody reads at sub-minute precision). Write only when something the rest of the
        // system depends on changed: the IP (clone detection keys off the previous last_ip),
        // the app version (admin device list), or when last_seen_at is older than the
        // interval ("seen in the last N minutes" is all the admin UI needs).
        $requestIp = $request->ip();
        $appVersion = $request->header('X-Launcher-Version', $device->app_version);
        $seenIsStale = $device->last_seen_at === null
            || $device->last_seen_at->lt(now()->subMinutes(self::LAST_SEEN_WRITE_INTERVAL_MINUTES));

        if ($seenIsStale || $device->last_ip !== $requestIp || $device->app_version !== $appVersion) {
            $device->forceFill([
                'last_seen_at' => now(),
                'last_ip' => $requestIp,
                'app_version' => $appVersion,
            ])->save();
        }

        $request->attributes->set('launcher_device', $device);

        return $next($request);
    }

    /**
     * Writes a row only when this device shows up on a different network than last
     * time. Runs before last_ip/last_seen_at are overwritten above, because both of
     * those are the "previous" values it compares against.
     *
     * Nothing is recorded on the common path -- a device that stays on one network
     * writes one row when it first appears and then never again -- so this costs an
     * in-memory comparison per request and a row only on an actual change.
     */
    private function recordNetworkSwitch(LauncherDevice $device, ?string $ip): void
    {
        $network = LauncherDeviceNetworkSwitch::networkOf($ip);
        if ($network === null) {
            return;
        }

        $previousNetwork = LauncherDeviceNetworkSwitch::networkOf($device->last_ip);
        if ($previousNetwork === $network) {
            return;
        }

        try {
            LauncherDeviceNetworkSwitch::query()->create([
                'launcher_device_id' => $device->getKey(),
                'user_id' => $device->user_id,
                'network' => $network,
                'previous_network' => $previousNetwork,
                'seconds_since_previous' => $device->last_seen_at
                    ? max(0, $device->last_seen_at->diffInSeconds(now()))
                    : null,
            ]);
        } catch (QueryException $exception) {
            // Telemetry must never break a paid customer's session. If this table is
            // missing (migration not run yet) or the write fails, the request carries
            // on exactly as it did before this feature existed.
            Log::warning('launcher.device.network_switch_not_recorded', [
                'device_id' => $device->getKey(),
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
