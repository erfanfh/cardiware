<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Notification\GetRecentPurchasesAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RecentPurchasesController extends Controller
{
    public function __invoke(GetRecentPurchasesAction $purchases): JsonResponse
    {
        return response()->json(['purchases' => $purchases->execute()])
            ->header('Cache-Control', 'no-store');
    }
}
