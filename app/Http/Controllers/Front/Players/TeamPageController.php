<?php

namespace App\Http\Controllers\Front\Players;

use App\Application\Players\Actions\GetPglSquadAction;
use Illuminate\View\View;

class TeamPageController
{
    public function __invoke(int $team, GetPglSquadAction $squadAction): View
    {
        return view('front.players.team', $squadAction->execute($team));
    }
}
