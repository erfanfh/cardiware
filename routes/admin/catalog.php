<?php

use App\Http\Controllers\Admin\Catalog\ProductAttributeController;
use App\Http\Controllers\Admin\Catalog\ProductController;

Route::resource('products', ProductController::class)->except(['show']);

Route::get('products/attributes', ProductAttributeController::class)->name('products.attributes.index');
