<?php

use App\Http\Controllers\Admin\Catalog\ProductController;

Route::resource('products', ProductController::class)->except(['show']);
