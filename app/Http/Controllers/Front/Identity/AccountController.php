<?php

namespace App\Http\Controllers\Front\Identity;

use App\Application\Content\Actions\GetYesterdayTopPostsAction;
use App\Application\Identity\Actions\GetAccountUserStatsAction;
use App\Application\Identity\Actions\GetUserAvatarAction;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(
        GetYesterdayTopPostsAction $getYesterdayTopPostsAction,

    ): View
    {
        $yesterdayTopPosts = $getYesterdayTopPostsAction->execute();

        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.dashboard', compact(
            'user',
            'yesterdayTopPosts'
        ));
    }

    public function downloads(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.downloads', compact('user'));
    }

    public function orders(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.orders', compact('user'));
    }

    public function tickets(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.tickets', compact('user'));
    }

    public function favorites(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.favorites', compact('user'));
    }

    public function information(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.information', compact('user'));
    }

    public function security(): View
    {
        $user = $this->getUser();
        $this->hydrateUser($user);

        return view('front.account.pages.security', compact('user'));
    }

    private function getUser(): User
    {
        return auth()->user();
    }

    private function hydrateUser(User $user): void
    {
        $stats = app(GetAccountUserStatsAction::class)->execute($user);
        $avatar = app(GetUserAvatarAction::class)->execute($user);

        $user->setAttribute('paid_orders_count', $stats['paid_orders_count']);
        $user->setAttribute('purchase_accesses_count', $stats['purchase_accesses_count']);

        $user->setAttribute('avatar_url', $avatar['url']);
    }
}
