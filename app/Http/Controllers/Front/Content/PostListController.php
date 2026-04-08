<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Content\Models\Post;

class PostListController
{
    public function __invoke()
    {
        $posts = Post::paginate(9);

        return view('front.posts.list', compact('posts'));
    }
}
