<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Identity\LoginRequest;
use App\Http\Requests\Api\V1\Identity\LoginWithLauncherOtpRequest;
use App\Http\Requests\Api\V1\Identity\RequestLauncherLoginOtpRequest;
use App\Http\Resources\Api\V1\Identity\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Application\Launcher\Exceptions\DeviceLimitReachedException;
use App\Application\Launcher\Exceptions\DeviceFingerprintMismatchException;
use App\Application\Launcher\Exceptions\DeviceTransferRequiredException;
use App\Application\Launcher\Actions\Security\VerifyDeviceProofAction;
use App\Application\Identity\Actions\Auth\ConsumeOtpAction;
use App\Application\Identity\Actions\Otp\RequestOtpAction;
use App\Application\Identity\Actions\Auth\GetActiveUserByMobileAction;
use App\Application\Identity\Actions\Auth\RequestLoginOtpAction;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Launcher\Models\LauncherDevice;
use Illuminate\Support\Facades\DB;
use DomainException;
use App\Application\Launcher\Exceptions\LauncherAccountSuspendedException;

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
    public function login(
        LoginRequest $request,
        VerifyDeviceProofAction $verifyDeviceProof,
        RequestOtpAction $requestOtp,
        ConsumeOtpAction $consumeOtp,
    ): JsonResponse
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

        $this->ensureLauncherAccess($user);

        $deviceData = $data['device'];
        $verifyDeviceProof->execute($deviceData);

        $isTransfer = $this->isDeviceTransfer($user, $deviceData, $request->ip());

        if ($isTransfer && empty($data['device_transfer_code'])) {
            if (blank($user->mobile)) {
                throw ValidationException::withMessages([
                    'device_transfer_code' => 'برای انتقال دستگاه باید شماره موبایل حساب توسط پشتیبانی ثبت شود.',
                ]);
            }

            $otp = $requestOtp->execute($user->mobile, OtpTypeEnum::DEVICE_TRANSFER);
            throw new DeviceTransferRequiredException($otp->sent, $otp->retryAfterSeconds);
        }

        $complete = fn(): array => $this->completeDeviceLogin(
            $user->getKey(),
            $deviceData,
            $request->ip(),
            $isTransfer,
        );

        [$device, $token, $freshUser] = $isTransfer
            ? $consumeOtp->execute(
                $user->mobile,
                (string)$data['device_transfer_code'],
                OtpTypeEnum::DEVICE_TRANSFER,
                fn() => $complete(),
            )
            : $complete();

        return $this->loginResponse($device, $token, $freshUser);
    }

    public function requestLoginOtp(
        RequestLauncherLoginOtpRequest $request,
        RequestLoginOtpAction $requestLoginOtp,
        GetActiveUserByMobileAction $getActiveUserByMobile,
    ): JsonResponse {
        try {
            $mobile = (string)$request->validated('mobile');
            $user = $getActiveUserByMobile->execute($mobile);
            $this->ensureLauncherAccess($user);
            $result = $requestLoginOtp->execute($mobile);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'otp_request_failed',
            ], 422);
        }

        $status = $result->sent || $result->alreadySent ? 200 : 503;

        return response()->json([
            'message' => $result->message,
            'data' => [
                'sent' => $result->sent,
                'already_sent' => $result->alreadySent,
                'retry_after_seconds' => $result->retryAfterSeconds,
                'expires_after_seconds' => $result->expiresAfterSeconds,
            ],
        ], $status);
    }

    public function loginWithOtp(
        LoginWithLauncherOtpRequest $request,
        VerifyDeviceProofAction $verifyDeviceProof,
        ConsumeOtpAction $consumeOtp,
        GetActiveUserByMobileAction $getActiveUserByMobile,
    ): JsonResponse {
        $data = $request->validated();

        try {
            $user = $getActiveUserByMobile->execute((string)$data['mobile']);
            $this->ensureLauncherAccess($user);
            $deviceData = $data['device'];
            $verifyDeviceProof->execute($deviceData);
            $isTransfer = $this->isDeviceTransfer($user, $deviceData, $request->ip());

            // The login OTP itself confirms ownership of the account mobile and therefore
            // also authorizes the one permitted device transfer.
            [$device, $token, $freshUser] = $consumeOtp->execute(
                $user->mobile,
                (string)$data['otp_code'],
                OtpTypeEnum::LOGIN,
                function () use ($user, $deviceData, $request, $isTransfer): array {
                    $user->forceFill([
                        'mobile_verified_at' => $user->mobile_verified_at ?? now(),
                    ])->save();

                    return $this->completeDeviceLogin(
                        $user->getKey(),
                        $deviceData,
                        $request->ip(),
                        $isTransfer,
                    );
                },
            );
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'code' => 'invalid_otp',
            ], 422);
        }

        return $this->loginResponse($device, $token, $freshUser);
    }

    private function isDeviceTransfer(User $user, array $deviceData, ?string $ip = null): bool
    {
        $activeSlot = (int)($user->active_device_slot ?: 0);
        $activeKeyFingerprint = $this->publicKeyFingerprintForSlot($user, $activeSlot);
        $isFirstActivation = $activeSlot === 0 || blank($activeKeyFingerprint);
        $isActiveDevice = !$isFirstActivation && hash_equals(
            (string)$activeKeyFingerprint,
            (string)$deviceData['public_key_fingerprint']
        );

        if ($isActiveDevice) {
            $registeredHardware = $activeSlot === 2
                ? $user->second_device_fingerprint
                : $user->first_device_fingerprint;
            if ($registeredHardware !== null
                && !hash_equals($registeredHardware, (string)$deviceData['fingerprint_hash'])) {
                throw new DeviceFingerprintMismatchException();
            }
        }

        $isTransfer = !$isFirstActivation && !$isActiveDevice;
        // Emergency-offline mode hands out licences of up to 30 days; moving an account to
        // a new machine meanwhile would let one purchase run on two PCs for that long.
        // Covers both the password and the SMS login paths (both call this method).
        if ($isTransfer && app(\App\Application\Launcher\Support\LauncherEmergencyOffline::class)->isEnabled()) {
            $this->logBlockedTransferAttempt($user, $deviceData, $ip, 'emergency_paused');
            throw new \App\Application\Launcher\Exceptions\DeviceTransferPausedException();
        }
        if ($isTransfer && $user->device_transfer_used) {
            $this->logBlockedTransferAttempt($user, $deviceData, $ip, 'transfer_already_used');
            throw new DeviceLimitReachedException(1, []);
        }

        return $isTransfer;
    }

    /**
     * Every blocked third-device attempt is recorded here so support/admin staff can
     * proactively spot likely-shared accounts (repeated attempts from different device
     * fingerprints) instead of only finding out when a user complains they were locked
     * out -- see admin.launcher-devices.index, "watchlist" section.
     *
     * @param array<string, mixed> $deviceData
     */
    private function logBlockedTransferAttempt(
        User $user,
        array $deviceData,
        ?string $ip,
        string $reason,
    ): void {
        DB::table('launcher_device_transfer_blocks')->insert([
            'user_id' => $user->getKey(),
            'reason' => $reason,
            'attempted_device_id' => $deviceData['id'] ?? null,
            'attempted_device_name' => $deviceData['name'] ?? null,
            'attempted_platform' => $deviceData['platform'] ?? null,
            'attempted_public_key_fingerprint' => $deviceData['public_key_fingerprint'] ?? null,
            'attempted_fingerprint_hash' => $deviceData['fingerprint_hash'] ?? null,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    private function loginResponse(LauncherDevice $device, mixed $token, User $user): JsonResponse
    {
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
                    'slot' => $device->device_slot,
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $deviceData
     * @return array{0: LauncherDevice, 1: mixed, 2: User}
     */
    private function completeDeviceLogin(
        int $userId,
        array $deviceData,
        ?string $ip,
        bool $transfer,
    ): array {
        return DB::transaction(function () use ($userId, $deviceData, $ip, $transfer): array {
            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $this->ensureLauncherAccess($user);
            $slot = (int)($user->active_device_slot ?: 0);
            $activeFingerprint = $this->publicKeyFingerprintForSlot($user, $slot);
            $incomingFingerprint = (string)$deviceData['public_key_fingerprint'];

            if ($slot === 0 || blank($activeFingerprint)) {
                $slot = 1;
                $user->forceFill([
                    'first_device_public_key' => $deviceData['public_key'],
                    'first_device_fingerprint' => $deviceData['fingerprint_hash'],
                    'active_device_slot' => 1,
                    'device_transfer_used' => false,
                    'first_activated_at' => now(),
                    'transferred_at' => null,
                ])->save();
            } elseif (!hash_equals((string)$activeFingerprint, $incomingFingerprint)) {
                if (!$transfer || $user->device_transfer_used) {
                    $this->logBlockedTransferAttempt(
                        $user,
                        $deviceData,
                        $ip,
                        $transfer ? 'transfer_already_used' : 'transfer_not_authorized',
                    );
                    throw new DeviceLimitReachedException(1, []);
                }

                $slot = 2;
                $user->forceFill([
                    'second_device_public_key' => $deviceData['public_key'],
                    'second_device_fingerprint' => $deviceData['fingerprint_hash'],
                    'active_device_slot' => 2,
                    'device_transfer_used' => true,
                    'transferred_at' => now(),
                ])->save();
            }

            $oldDevices = LauncherDevice::query()
                ->where('user_id', $userId)
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->get();
            foreach ($oldDevices as $oldDevice) {
                if ((string)$oldDevice->public_key_fingerprint !== $incomingFingerprint) {
                    $oldDevice->forceFill(['revoked_at' => now()])->save();
                    $oldDevice->tokens()->delete();
                }
            }

            $device = LauncherDevice::query()->updateOrCreate(
                ['user_id' => $userId, 'device_id' => $deviceData['id']],
                [
                    'name' => $deviceData['name'],
                    'fingerprint_hash' => $deviceData['fingerprint_hash'],
                    'public_key' => $deviceData['public_key'],
                    'public_key_fingerprint' => $incomingFingerprint,
                    'device_slot' => $slot,
                    'platform' => $deviceData['platform'],
                    'app_version' => $deviceData['app_version'],
                    'last_ip' => $ip,
                    'last_seen_at' => now(),
                    'revoked_at' => null,
                ]
            );
            $device->tokens()->delete();

            $token = $user->createToken(
                name: 'MW Launcher — ' . $device->name,
                abilities: ['windows-app', 'user:read', 'library:read', 'downloads:prepare'],
                expiresAt: now()->addDays(max(1, (int)config('launcher.token_ttl_days', 30))),
            );
            $token->accessToken->forceFill(['launcher_device_id' => $device->getKey()])->save();

            return [$device, $token, $user];
        });
    }

    private function publicKeyFingerprintForSlot(User $user, int $slot): ?string
    {
        $encoded = $slot === 2
            ? $user->second_device_public_key
            : $user->first_device_public_key;
        if (blank($encoded)) {
            return null;
        }

        $der = base64_decode((string)$encoded, true);
        return $der === false ? null : hash('sha256', $der);
    }

    private function ensureLauncherAccess(User $user): void
    {
        if ($user->isLauncherSuspended()) {
            throw new LauncherAccountSuspendedException($user);
        }
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
