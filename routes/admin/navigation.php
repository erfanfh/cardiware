<?php

use App\Http\Controllers\Admin\Navigation\NavigationMenuController;

Route::resource('navigation-menus', NavigationMenuController::class)->names('navigation-menus')->only(['edit', 'update']);
