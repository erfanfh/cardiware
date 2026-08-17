<?php

namespace App\Http\Controllers\Front\Raffle;

use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Application\Raffle\Actions\Front\GetAccountRaffleOverviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Raffle\AccountDrawCampaignRequest;
use Illuminate\Contracts\View\View;

class AccountDrawCampaignController extends Controller
{
    public function __invoke(
        AccountDrawCampaignRequest $request,
        HydrateAccountUserAction $hydrateAccountUserAction,
        GetAccountRaffleOverviewAction $action,
    ): View {
        $user = $hydrateAccountUserAction->execute($request->user());
        $data = $action->execute($user, $request->validated('campaign'));

        return view('front.account.pages.raffles.index', [
            'user' => $user,
            ...$data,
        ]);
    }
}
