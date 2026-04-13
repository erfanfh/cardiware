<?php

use App\Http\Controllers\Admin\Navigation\NavigationMenuController;

Route::resource('navigationMenus', NavigationMenuController::class)->names('navigation-menus')->only(['edit', 'update']);
