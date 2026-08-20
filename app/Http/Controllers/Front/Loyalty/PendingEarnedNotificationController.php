<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Notifications\PendingLoyaltyEarnedNotifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendingEarnedNotificationController extends Controller
{
    public function __invoke(
        Request $request,
        PendingLoyaltyEarnedNotifications $pendingNotifications,
    ): JsonResponse {
        return response()
            ->json([
                'notifications' => $pendingNotifications->pull($request),
            ])
            ->withoutCookie(PendingLoyaltyEarnedNotifications::CLIENT_COOKIE);
    }
}
