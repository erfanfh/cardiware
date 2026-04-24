<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Content\Models\Post;

class PostListController
{
    public function __invoke()
    {
        $postsPerPage = get_setting('posts', 'posts_per_page') ?: 12;
        $posts = Post::latest()->paginate($postsPerPage);
        return view('front.posts.list', compact('posts'));
    }
}
