<?php

namespace App\Http\Controllers\Admin\Loyalty;

use App\Application\Loyalty\Actions\Reward\HandleRewardOfferBulkAction;
use App\Http\Requests\Admin\Loyalty\BulkRewardOfferActionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class RewardOfferBulkActionController
{
    /** @throws Throwable */
    public function __invoke(BulkRewardOfferActionRequest $request, HandleRewardOfferBulkAction $handler): RedirectResponse
    {
        Gate::authorize(
            $request->bulkAction() === 'delete' ? 'loyalty-reward.delete' : 'loyalty-reward.edit'
        );

        $result = $handler->execute($request->bulkAction(), $request->rewardOfferIds());

        return redirect()->route('admin.reward-offers.index')
            ->with('success', $handler->successMessage($result));
    }
}
