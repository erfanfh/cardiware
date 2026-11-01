<?php

/*
|--------------------------------------------------------------------------
| Players — بخش بازیکنان پچ PGL (دیتابیس پچ)
|--------------------------------------------------------------------------
| هاب تیم‌ها، صفحه‌ی هر تیم (ترکیب + نشان تغییرات آخرین آپدیت) و
| صفحه‌ی هر بازیکن (آمار + تاریخچه تغییرات).
| داده‌ها با artisan pgl:import-players از خروجی pgl6_db_export.py وارد می‌شوند.
*/

use App\Http\Controllers\Front\Players\PlayerPageController;
use App\Http\Controllers\Front\Players\PlayersHubController;
use App\Http\Controllers\Front\Players\TeamPageController;
use Illuminate\Support\Facades\Route;

Route::get('/players', PlayersHubController::class)->name('players.hub');
Route::get('/players/t/{team}', TeamPageController::class)
    ->whereNumber('team')->name('players.team');
Route::get('/players/p/{player}', PlayerPageController::class)
    ->whereNumber('player')->name('players.player');
