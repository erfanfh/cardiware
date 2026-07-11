<?php

namespace App\Http\Controllers\Api\V1\Identity;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Identity\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Get current user
     *
     * Returns the authenticated user's account information.
     *
     * The launcher can call this endpoint during startup to verify whether
     * the locally stored access token is still valid.
     *
     * @group User
     *
     * @responseFile status=200 scenario="Authenticated user" storage/responses/api/v1/user/me.success.json
     * @responseFile status=401 scenario="Unauthenticated" storage/responses/api/v1/errors/unauthenticated.json
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => UserResource::make($request->user()),
        ]);
    }
}
