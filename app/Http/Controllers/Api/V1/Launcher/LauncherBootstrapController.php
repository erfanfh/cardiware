<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherBootstrapAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Launcher\LauncherBootstrapRequest;
use Illuminate\Http\JsonResponse;

class LauncherBootstrapController extends Controller
{
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
