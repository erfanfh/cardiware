<?php

namespace App\Http\Middleware;

use App\Application\Launcher\Exceptions\DeviceRevokedException;
use App\Domain\Launcher\Models\LauncherDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLauncherDeviceIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if ($token === null || !$token->can('windows-app')) {
            throw new DeviceRevokedException();
        }

        $deviceId = $token->getAttribute('launcher_device_id');
        $device = $deviceId
            ? LauncherDevice::query()
                ->whereKey($deviceId)
                ->where('user_id', $request->user()->getKey())
                ->first()
            : null;

        if (!$device instanceof LauncherDevice || !$device->isActive()) {
            $token->delete();
            throw new DeviceRevokedException();
        }

        $device->forceFill([
            'last_seen_at' => now(),
            'last_ip' => $request->ip(),
            'app_version' => $request->header('X-Launcher-Version', $device->app_version),
        ])->save();

        $request->attributes->set('launcher_device', $device);

        return $next($request);
    }
}
