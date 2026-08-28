<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Notifications\LoyaltyEarnedNotificationFactory;
use App\Application\Loyalty\TimeSpent\TimeSpentSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompleteTimeSpentController extends Controller
{
    public function __construct(
        private readonly TimeSpentSessionService $service,
        private readonly LoyaltyEarnedNotificationFactory $notificationFactory,
    ) {}

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $result = $this->service->complete(
            $request->user(),
            $data['token']
        );

        return response()->json([
            'status' => $result->session->status->value,
            'earned_points' => $result->earnedPoints,
            'notification' => $result->entry
                ? $this->notificationFactory->forTimeSpent(
                    $result->entry,
                    $result->session,
                )
                : null,
        ]);
    }
}
