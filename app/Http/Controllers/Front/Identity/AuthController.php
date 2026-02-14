<?php

namespace App\Http\Controllers\Front\Identity;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function auth(Request $request): View|RedirectResponse
    {
        return view('front.identity.users.auth.auth');
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.index');
    }

    public function resetPassword()
    {
        return view('front.identity.users.auth.reset-password');
    }
}
