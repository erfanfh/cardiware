<?php

namespace App\Http\Controllers\Admin\Marketplace;

use App\Http\Controllers\Admin\Identity\UserSearchController;
use Illuminate\Support\Facades\Gate;

class StoreSellerSearchController extends UserSearchController
{
    protected function authorizeSearch(): void
    {
        abort_unless(Gate::any(['store.create', 'store.edit']), 403);
    }
}
