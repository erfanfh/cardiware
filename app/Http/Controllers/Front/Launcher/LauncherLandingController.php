<?php

namespace App\Http\Controllers\Front\Launcher;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class LauncherLandingController extends Controller
{
    public function __invoke(): View
    {
        return view('front.launcher.landing', [
            'downloadUrl' => config('launcher.public_download_url'),
        ]);
    }
}
