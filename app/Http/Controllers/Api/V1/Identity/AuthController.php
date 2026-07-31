<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\LoginRequest;
use App\Http\Resources\Api\V1\Identity\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Application\Launcher\Exceptions\DeviceLimitReachedException;
use App\Application\Launcher\Exceptions\DeviceFingerprintMismatchException;
use App\Domain\Launcher\Models\LauncherDevice;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Authenticate user
     *
     * Authenticates a user using an email address or mobile number and password.
     *
     * A new personal access token is issued for the supplied Windows device.
     * The returned token must be securely stored and sent as a Bearer token
     * with protected API requests.
     *
     * @group Authentication
     * @unauthenticated
     *
     * @responseFile status=200 scenario="Login successful" storage/responses/api/v1/auth/login.success.json
     * @responseFile status=422 scenario="Invalid credentials or validation failed" storage/responses/api/v1/errors/validation.json
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()
            ->where('email', $data['login'])
            ->orWhere('mobile', $data['login'])
            ->first();


        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'The provided credentials are incorrect.',
            ]);
        }

        $deviceData = $data['device'];
        $maximum = max(1, (int)config('launcher.max_active_devices_per_user', 2));

        [$device, $token] = DB::transaction(function () use (
            $user,
            $deviceData,
            $maximum,
            $request,
        ): array {
            /*
             * Lock the account row so concurrent first-time logins from
             * different PCs cannot bypass the active-device limit.
             */
            User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $device = LauncherDevice::query()
                ->where('user_id', $user->getKey())
                ->where('device_id', $deviceData['id'])
                ->lockForUpdate()
                ->first();

            if (!$device instanceof LauncherDevice) {
                $activeDevices = LauncherDevice::query()
                    ->where('user_id', $user->getKey())
                    ->whereNull('revoked_at')
                    ->lockForUpdate()
                    ->get();

                if ($activeDevices->count() >= $maximum) {
                    throw new DeviceLimitReachedException(
                        maximum: $maximum,
                        devices: $activeDevices->map(fn(LauncherDevice $item): array => [
                            'id' => $item->device_id,
                            'name' => $item->name,
                            'last_seen_at' => $item->last_seen_at?->toISOString(),
                        ])->all(),
                    );
                }

                $device = LauncherDevice::query()->create([
                    'user_id' => $user->getKey(),
                    'device_id' => $deviceData['id'],
                    'name' => $deviceData['name'],
                    'fingerprint_hash' => $deviceData['fingerprint_hash'] ?? null,
                    'platform' => $deviceData['platform'],
                    'app_version' => $deviceData['app_version'],
                    'last_ip' => $request->ip(),
                    'last_seen_at' => now(),
                ]);
            } elseif (
                filled($device->fingerprint_hash)
                && !hash_equals(
                    (string)$device->fingerprint_hash,
                    (string)$deviceData['fingerprint_hash']
                )
            ) {
                throw new DeviceFingerprintMismatchException();
            } elseif (!$device->isActive()) {
                $activeCount = LauncherDevice::query()
                    ->where('user_id', $user->getKey())
                    ->whereNull('revoked_at')
                    ->lockForUpdate()
                    ->count();

                if ($activeCount >= $maximum) {
                    throw new DeviceLimitReachedException($maximum, []);
                }
            }

            $device->forceFill([
                'name' => $deviceData['name'],
                'fingerprint_hash' => $deviceData['fingerprint_hash'] ?? null,
                'platform' => $deviceData['platform'],
                'app_version' => $deviceData['app_version'],
                'last_ip' => $request->ip(),
                'last_seen_at' => now(),
                'revoked_at' => null,
            ])->save();

            $device->tokens()->delete();

            $token = $user->createToken(
                name: 'MW Launcher — ' . $device->name,
                abilities: [
                    'windows-app',
                    'user:read',
                    'library:read',
                    'downloads:prepare',
                ],
                expiresAt: now()->addDays(
                    max(1, (int)config('launcher.token_ttl_days', 30))
                ),
            );

            $token->accessToken->forceFill([
                'launcher_device_id' => $device->getKey(),
            ])->save();

            return [$device, $token];
        });

        return response()->json([
            'message' => 'Login completed successfully.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
                'user' => UserResource::make($user),
                'device' => [
                    'id' => $device->device_id,
                    'name' => $device->name,
                    'last_seen_at' => $device->last_seen_at?->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Log out current device
     *
     * Revokes the personal access token used for the current request.
     *
     * After this request succeeds, the same token can no longer be used
     * to access protected endpoints.
     *
     * @group Authentication
     *
     * @responseFile status=200 scenario="Logout successful" storage/responses/api/v1/auth/logout.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout completed successfully.',
        ]);
    }
}
