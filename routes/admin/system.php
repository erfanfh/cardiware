<?php

use App\Http\Controllers\Admin\System\SettingController;
use App\Http\Controllers\Admin\System\TeamSettingMemberSearchController;

Route::prefix('settings')->name('settings.')->controller(SettingController::class)->group(function () {
    Route::get('backup', 'backup')->name('backup');
    Route::put('backup', 'storeBackup')->name('backup.store');
    Route::post('backup/run', 'runBackup')->name('backup.run');
    Route::get('raffle', 'raffle')->name('raffle');
    Route::put('raffle', 'storeRaffle')->name('raffle.store');
    Route::get('loyalty', 'loyalty')->name('loyalty');
    Route::put('loyalty', 'storeLoyalty')->name('loyalty.store');
    Route::get('general', 'general')->name('general');
    Route::put('general', 'storeGeneral')->name('general.store');
    Route::get('seo', 'seo')->name('seo');
    Route::put('seo', 'storeSeo')->name('seo.store');
    Route::get('header', 'header')->name('header');
    Route::put('header', 'storeHeader')->name('header.store');
    Route::get('footer', 'footer')->name('footer');
    Route::put('footer', 'storeFooter')->name('footer.store');
    Route::get('home', 'home')->name('home');
    Route::put('home', 'storeHome')->name('home.store');
    Route::get('about-us', 'aboutUs')->name('about-us');
    Route::put('about-us', 'storeAboutUs')->name('about-us.store');
    Route::get('team', 'team')->name('team');
    Route::put('team', 'storeTeam')->name('team.store');
    Route::get('sidebar-widgets', 'sidebarWidgets')->name('sidebar-widgets');
    Route::put('sidebar-widgets', 'storeSidebarWidgets')->name('sidebar-widgets.store');
    Route::get('auth', 'auth')->name('auth');
    Route::put('auth', 'storeAuth')->name('auth.store');
    Route::get('projects', 'projects')->name('projects');
    Route::put('projects', 'storeProjects')->name('projects.store');
    Route::get('posts', 'posts')->name('posts');
    Route::put('posts', 'storePosts')->name('posts.store');
    Route::get('products', 'products')->name('products');
    Route::put('products', 'storeProducts')->name('products.store');
    Route::get('videos', 'videos')->name('videos');
    Route::put('videos', 'storeVideos')->name('videos.store');
    Route::get('ticket', 'ticket')->name('ticket');
    Route::put('ticket', 'storeTicket')->name('ticket.store');
});

Route::get('settings/team/ajax-members', TeamSettingMemberSearchController::class)->name('settings.team.ajax-members');
