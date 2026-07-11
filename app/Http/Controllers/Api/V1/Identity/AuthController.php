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

        $deviceName = $data['device_name'] ?? 'Windows App';

        $token = $user->createToken(
            name: $deviceName,
            abilities: [
                'windows-app',
                'user:read',
                'library:read',
                'downloads:prepare',
            ],
            expiresAt: now()->addDays(30),
        );

        return response()->json([
            'message' => 'Login completed successfully.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
                'user' => UserResource::make($user),
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
