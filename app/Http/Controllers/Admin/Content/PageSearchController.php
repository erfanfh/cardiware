<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Page;
use App\Http\Controllers\Admin\AdminSearchController;

class PageSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Page::class;
    }

    protected function permissionName(): string
    {
        return 'page.list';
    }
}
