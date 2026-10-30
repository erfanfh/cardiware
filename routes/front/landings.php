<?php

/*
|--------------------------------------------------------------------------
| Product Landing Pages — صفحات لندینگ محصولات PGL (دوزبانه fa/en)
|--------------------------------------------------------------------------
| موتور لندینگ محصول: هر محصول یک کلید در config/product-landings.php
| و یک تم رنگی دارد؛ دیتای لیگ/تیم/استادیوم/جام مشترک است و از
| config/landing-catalog.php می‌آید.
|
| آدرس‌ها:  /{landing}  و  /{landing}/{page}  و  /{landing}/leagues/{league}
|           و  /{landing}/leagues/{league}/{team}   — نسخه انگلیسی با /en
|
| ⚠️ ثبت این فایل حتماً باید قبل از require فایل content.php در
|    routes/front.php باشد (روت کچ‌اَل /{slug} آنجا آخرین روت است).
*/

use App\Http\Controllers\Front\Landings\LandingPageController;
use App\Http\Controllers\Front\Landings\ProductLandingController;
use App\Http\Middleware\EnsureUserIsSuperAdmin;
use App\Http\Middleware\ProtectContentPreview;
use Illuminate\Support\Facades\Route;

// Match only registered products so the CMS catch-all keeps its own slugs.
$landingPattern = implode('|', array_map(
    static fn (string $slug): string => preg_quote($slug, '#'),
    array_keys(config('product-landings.landings', [])),
)) ?: '(?!)';

/* --- فارسی (بدون پیشوند) --- */
Route::get('/{landing}', [ProductLandingController::class, 'show'])
    ->where('landing', $landingPattern)
    ->middleware([EnsureUserIsSuperAdmin::class, ProtectContentPreview::class])
    ->name('landings.show');

Route::get('/{landing}/leagues/{league}', [LandingPageController::class, 'league'])
    ->where('landing', $landingPattern)
    ->where('league', '[a-z0-9][a-z0-9-]*')
    ->name('landings.league');

Route::get('/{landing}/leagues/{league}/{team}', [LandingPageController::class, 'team'])
    ->where('landing', $landingPattern)
    ->where('league', '[a-z0-9][a-z0-9-]*')
    ->where('team', '[a-z0-9][a-z0-9-]*')
    ->name('landings.team');

Route::get('/{landing}/{page}', [LandingPageController::class, 'show'])
    ->where('landing', $landingPattern)
    ->where('page', '[a-z0-9][a-z0-9-]*')
    ->name('landings.page');

/* --- انگلیسی (پیشوند /en) --- */
Route::prefix('en')->name('en.')->group(function () use ($landingPattern) {
    Route::get('/{landing}', [ProductLandingController::class, 'show'])
        ->where('landing', $landingPattern)
        ->name('landings.show');

    Route::get('/{landing}/leagues/{league}', [LandingPageController::class, 'league'])
        ->where('landing', $landingPattern)
        ->where('league', '[a-z0-9][a-z0-9-]*')
        ->name('landings.league');

    Route::get('/{landing}/leagues/{league}/{team}', [LandingPageController::class, 'team'])
        ->where('landing', $landingPattern)
        ->where('league', '[a-z0-9][a-z0-9-]*')
        ->where('team', '[a-z0-9][a-z0-9-]*')
        ->name('landings.team');

    Route::get('/{landing}/{page}', [LandingPageController::class, 'show'])
        ->where('landing', $landingPattern)
        ->where('page', '[a-z0-9][a-z0-9-]*')
        ->name('landings.page');
});
