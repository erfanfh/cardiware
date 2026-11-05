<?php

namespace App\Http\Controllers\Api\V1\Launcher;

use App\Application\Launcher\Actions\GetLauncherClubSummaryAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET launcher/club — امتیاز، سطح، رتبه و وضعیت VIP کاربر برای کارت پروفایل لانچر.
 */
final class LauncherClubController extends Controller
{
    public function __invoke(Request $request, GetLauncherClubSummaryAction $summary): JsonResponse
    {
        return response()
            ->json(['data' => $summary->execute($request->user())])
            ->header('Cache-Control', 'no-store, private');
    }
}
