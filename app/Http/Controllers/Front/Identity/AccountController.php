<?php

namespace App\Http\Controllers\Front\Identity;

use App\Application\Content\Actions\GetYesterdayTopPostsAction;
use App\Application\Identity\Actions\HydrateAccountUserAction;
use App\Domain\Identity\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private readonly HydrateAccountUserAction $hydrateAccountUserAction,
    )
    {
    }

    public function dashboard(
        GetYesterdayTopPostsAction $getYesterdayTopPostsAction,
    ): View
    {
        $user = $this->getHydratedUser();

        $yesterdayTopPosts = $getYesterdayTopPostsAction->execute();

        return view('front.account.pages.dashboard', compact(
            'user',
            'yesterdayTopPosts'
        ));
    }

    public function downloads(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.downloads', compact('user'));
    }

    public function orders(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.orders', compact('user'));
    }

    public function tickets(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.tickets', compact('user'));
    }

    public function favorites(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.favorites', compact('user'));
    }

    public function information(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.information', compact('user'));
    }

    public function security(): View
    {
        $user = $this->getHydratedUser();

        return view('front.account.pages.security', compact('user'));
    }

    private function getHydratedUser(): User
    {
        $user = $this->getUser();

        return $this->hydrateAccountUserAction->execute($user);
    }

    private function getUser(): User
    {
        return auth()->user();
    }
}
