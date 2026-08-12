<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Application\Launcher\Exceptions\DeviceSelfServiceDisabledException;

class LauncherDeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentId = $request->attributes->get('launcher_device')?->id;

        $devices = LauncherDevice::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('revoked_at')
            ->latest('last_seen_at')
            ->get()
            ->map(fn(LauncherDevice $device): array => [
                'id' => $device->device_id,
                'name' => $device->name,
                'platform' => $device->platform,
                'app_version' => $device->app_version,
                'last_seen_at' => $device->last_seen_at?->toISOString(),
                'is_current' => $device->id === $currentId,
            ]);

        return response()->json(['data' => $devices]);
    }

    public function destroy(Request $request, string $deviceId): JsonResponse
    {
        throw new DeviceSelfServiceDisabledException();
    }
}
