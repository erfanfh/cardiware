<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\TimeSpent\TimeSpentSessionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CompleteTimeSpentController extends Controller
{
    public function __construct(
        private readonly TimeSpentSessionService $service,
    ) {
    }

    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
        ]);

        $session = $this->service->complete(
            $request->user(),
            $data['token']
        );

        return response()->json([
            'status' => $session->status->value,
        ]);
    }
}
