<?php

namespace App\Http\Controllers\Front\Content;

class PostListController
{
    public function __invoke()
    {
        return view('front.posts.list');
    }
}
