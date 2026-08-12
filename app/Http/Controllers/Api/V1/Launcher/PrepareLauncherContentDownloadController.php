<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\PrepareLauncherContentDownloadAction;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Download\PrepareDownloadRequest;
use Illuminate\Http\JsonResponse;

class PrepareLauncherContentDownloadController extends Controller
{
    public function __invoke(
        PrepareDownloadRequest $request,
        int $purchaseAccess,
        int $file,
        PrepareLauncherContentDownloadAction $action,
    ): JsonResponse {
        $device = $request->attributes->get('launcher_device');
        abort_unless($device instanceof LauncherDevice, 401);

        $contentType = (string)$request->route('contentType');
        abort_unless(in_array($contentType, ['update', 'mod'], true), 404);

        $contentId = $contentType === 'update'
            ? (int)$request->route('updateId')
            : (int)$request->route('modId');

        abort_unless($contentId > 0, 404);

        return response()->json([
            'message' => 'Download prepared successfully.',
            'data' => $action->execute(
                $request->user(),
                $device,
                $purchaseAccess,
                $contentType,
                $contentId,
                $file,
                $request->string('download_session_id')->toString(),
            ),
        ]);
    }
}
