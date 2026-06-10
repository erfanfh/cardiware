<?php

namespace App\Http\Controllers\Front\Identity;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\System\Models\PageViewDetail;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(): View
    {
        $user = $this->getUser();

        $yesterdayTopPosts = PageViewDetail::query()
            ->where('pageable_type', Post::class)
            ->whereDate('date', now()->subDay()->toDateString())
            ->where('count', '>', 0)
            ->with('pageable')
            ->orderByDesc('count')
            ->limit(3)
            ->get();

        return view('front.account.pages.dashboard', compact('user', 'yesterdayTopPosts'));
    }

    public function downloads(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.downloads', compact('user'));
    }

    public function orders(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.orders', compact('user'));
    }

    public function tickets(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.tickets', compact('user'));
    }

    public function favorites(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.favorites', compact('user'));
    }

    public function information(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.information', compact('user'));
    }

    public function security(): View
    {
        $user = $this->getUser();

        return view('front.account.pages.security', compact('user'));
    }

    private function getUser(): User
    {
        return auth()->user()
            ->load([
                'avatar.primaryVariant'
            ])
            ->loadCount([
                'orders as paid_orders_count' => fn($q) => $q->where('status', OrderStatusEnum::PAID),
                'purchaseAccesses'
            ]);
    }
}
