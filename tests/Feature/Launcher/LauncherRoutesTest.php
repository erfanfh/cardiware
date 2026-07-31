<?php

namespace Tests\Feature\Launcher;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LauncherRoutesTest extends TestCase
{
    public function test_launcher_api_routes_are_registered(): void
    {
        foreach ([
            'api.v1.auth.login',
            'api.v1.launcher.bootstrap',
            'api.v1.launcher.catalog.index',
            'api.v1.launcher.catalog.show',
            'api.v1.launcher.sale-items.claim',
            'api.v1.launcher.sale-items.checkout',
            'api.v1.launcher.checkout.consume',
            'api.v1.library.index',
            'api.v1.library.manifest',
            'api.v1.library.files.download',
            'api.v1.library.content.files.download',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Missing route: {$routeName}");
        }
    }

    public function test_signed_download_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('downloads.purchase-access.file'));
        $this->assertTrue(Route::has('downloads.launcher-content.file'));
        $this->assertTrue(Route::has('launcher.checkout.entry'));
    }
}
