<?php

namespace App\Http\Controllers\Front\Launcher;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class LauncherLandingController extends Controller
{
    public function __invoke(): View
    {
        return view('front.launcher.landing', [
            'downloadUrl' => $this->publicDownloadUrl(),
        ]);
    }

    private function publicDownloadUrl(): ?string
    {
        $stored = trim((string)get_setting('general', 'launcher_public_download_url', ''));
        $fallback = trim((string)config('launcher.public_download_url', ''));

        return $stored !== '' ? $stored : ($fallback !== '' ? $fallback : null);
    }
}
