<?php

namespace App\Http\Controllers\Front\Raffle;

use App\Application\Raffle\Actions\Front\GetPublicDrawCampaignDetailAction;
use App\Application\Raffle\Actions\Front\GetPublicDrawCampaignListAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Raffle\IndexDrawCampaignRequest;
use Illuminate\Contracts\View\View;

class PublicDrawCampaignController extends Controller
{
    public function index(
        IndexDrawCampaignRequest $request,
        GetPublicDrawCampaignListAction $action,
    ): View {
        $data = $action->execute($request->validated('status', 'all'));

        return view('front.raffles.index', $data);
    }

    public function show(
        string $slug,
        GetPublicDrawCampaignDetailAction $action,
    ): View {
        $data = $action->execute($slug, auth()->user());

        return view('front.raffles.show', $data);
    }
}
