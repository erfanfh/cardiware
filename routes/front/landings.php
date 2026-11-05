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

// Sub-pages also match pgl-* (landings added from the panel, e.g. pgl-6): no CMS
// route has two segments. The landing home of a panel landing (/pgl-6) is served by
// the CMS catch-all (ContentController) when no post or page owns that slug.
$subPagePattern = $landingPattern . '|pgl-[a-z0-9]+(?:-[a-z0-9]+)*';

/* --- فارسی (بدون پیشوند) --- */
Route::get('/{landing}', [ProductLandingController::class, 'show'])
    ->where('landing', $landingPattern)
    ->middleware([EnsureUserIsSuperAdmin::class, ProtectContentPreview::class])
    ->name('landings.show');

Route::get('/{landing}/leagues/{league}', [LandingPageController::class, 'league'])
    ->where('landing', $subPagePattern)
    ->where('league', '[a-z0-9][a-z0-9-]*')
    ->name('landings.league');

Route::get('/{landing}/leagues/{league}/{team}', [LandingPageController::class, 'team'])
    ->where('landing', $subPagePattern)
    ->where('league', '[a-z0-9][a-z0-9-]*')
    ->where('team', '[a-z0-9][a-z0-9-]*')
    ->name('landings.team');

Route::get('/{landing}/{page}', [LandingPageController::class, 'show'])
    ->where('landing', $subPagePattern)
    ->where('page', '[a-z0-9][a-z0-9-]*')
    ->name('landings.page');

/* --- انگلیسی (پیشوند /en) --- */
Route::prefix('en')->name('en.')->group(function () use ($subPagePattern) {
    $landingPattern = $subPagePattern;
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
