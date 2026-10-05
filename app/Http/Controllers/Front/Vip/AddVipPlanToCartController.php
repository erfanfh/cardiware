<?php

namespace App\Http\Controllers\Front\Vip;

use App\Application\Vip\Actions\AddVipPlanToCartAction;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class AddVipPlanToCartController extends Controller
{
    public function __invoke(VipPlan $vipPlan, AddVipPlanToCartAction $action): RedirectResponse
    {
        abort_unless($vipPlan->isPurchasableAvailable(), 404);

        $action->execute($vipPlan, auth()->user());

        return redirect()->route('cart.index')->with('success', 'پلن VIP به سبد خرید اضافه شد.');
    }
}
