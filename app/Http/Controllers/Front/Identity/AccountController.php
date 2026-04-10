<?php

namespace App\Http\Controllers\Front\Identity;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(): View
    {
        return view('front.account.dashboard');
    }
}
