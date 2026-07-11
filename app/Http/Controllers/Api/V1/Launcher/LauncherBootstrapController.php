<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherBootstrapAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Launcher\LauncherBootstrapRequest;
use Illuminate\Http\JsonResponse;

class LauncherBootstrapController extends Controller
{
    /**
     * Get launcher bootstrap configuration
     *
     * Returns launcher version information, update requirements, release channel,
     * maintenance status, and the official launcher download URL.
     *
     * This endpoint should be called whenever the Windows launcher starts.
     *
     * @group Launcher
     * @unauthenticated
     *
     * @responseFile status=200 scenario="Bootstrap configuration" storage/responses/api/v1/launcher/bootstrap.success.json
     * @responseFile status=422 scenario="Validation failed" storage/responses/api/v1/errors/validation.json
     */
    public function __invoke(
        LauncherBootstrapRequest   $request,
        GetLauncherBootstrapAction $action,
    ): JsonResponse
    {
        $data = $request->validated();

        $result = $action->execute(
            currentVersion: $data['version'],
            platform: $data['platform'],
            channel: $data['channel'],
        );

        return response()->json([
            'data' => $result,
        ]);
    }
}
