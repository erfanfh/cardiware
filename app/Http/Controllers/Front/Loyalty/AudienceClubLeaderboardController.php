<?php

namespace App\Http\Controllers\Front\Loyalty;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AudienceClubLeaderboardController extends Controller
{
    public function __invoke(
        Request $request,
        GetAudienceClubLeaderboardAction $getAudienceClubLeaderboardAction,
    ): View {
        $user = $request->user();
        $leaderboard = $getAudienceClubLeaderboardAction->execute($user, $request->query('period'));

        return view('front.loyalty.leaderboard', compact('user', 'leaderboard'));
    }
}
