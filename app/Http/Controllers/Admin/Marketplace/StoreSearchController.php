<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Domain\Marketplace\Models\Store;
use App\Http\Controllers\Admin\AdminSearchController;

class StoreSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Store::class;
    }

    protected function permissionName(): string
    {
        return 'store.list';
    }
}
