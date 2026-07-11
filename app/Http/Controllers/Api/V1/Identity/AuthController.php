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
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()
            ->where('email', $data['login'])
            ->orWhere('mobile', $data['login'])
            ->first();


        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'اطلاعات ورود صحیح نیست.',
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
            'message' => 'ورود با موفقیت انجام شد.',
            'data' => [
                'token_type' => 'Bearer',
                'access_token' => $token->plainTextToken,
                'expires_at' => $token->accessToken->expires_at?->toISOString(),
                'user' => UserResource::make($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'خروج با موفقیت انجام شد.',
        ]);
    }
}
