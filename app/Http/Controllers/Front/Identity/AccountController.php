<?php

namespace App\Http\Controllers\Front\Identity;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user()
            ->load([
                'avatar.primaryVariant'
            ])
            ->loadCount([
                'orders as paid_orders_count' => fn($q) => $q->where('status', OrderStatusEnum::PAID)
            ]);

        return view('front.account.dashboard', compact('user'));
    }
}
