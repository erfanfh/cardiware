<?php

namespace App\Http\Controllers\Admin\Content;

use App\Domain\Content\Models\Post;
use App\Http\Controllers\Admin\AdminSearchController;

class PostSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Post::class;
    }

    protected function permissionName(): string
    {
        return 'post.list';
    }
}
