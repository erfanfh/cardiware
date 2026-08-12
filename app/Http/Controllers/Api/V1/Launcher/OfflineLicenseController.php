<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\Security\IssueOfflineLicenseAction;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OfflineLicenseController extends Controller
{
    public function __invoke(
        Request $request,
        IssueOfflineLicenseAction $issueOfflineLicense,
    ): JsonResponse {
        $device = $request->attributes->get('launcher_device');
        abort_unless($device instanceof LauncherDevice, 403);

        return response()->json([
            'message' => 'مجوز آفلاین این دستگاه تا هفت روز تمدید شد.',
            'data' => $issueOfflineLicense->execute($request->user(), $device),
        ]);
    }
}
