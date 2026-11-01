<?php

namespace App\Http\Controllers\Front\Affiliate;

use App\Application\Affiliate\Actions\EnsureUserAffiliateAction;
use App\Application\Affiliate\Actions\GetAffiliateOverviewAction;
use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffiliatePanelController extends Controller
{
    public function __invoke(
        Request $request,
        EnsureUserAffiliateAction $ensureAffiliate,
        GetAffiliateOverviewAction $overviewAction,
        HydrateAccountUserAction $hydrateAccountUserAction,
    ): View {
        $user = $hydrateAccountUserAction->execute($request->user());
        $affiliate = $ensureAffiliate->execute($user);
        $overview = $overviewAction->execute($affiliate);

        return view('front.account.pages.affiliate', [
            'user' => $user,
            'affiliate' => $affiliate,
            'overview' => $overview,
            'referralUrl' => url('/') . '/?ref=' . $affiliate->code,
        ]);
    }
}
