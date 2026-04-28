<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostListAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostListController
{
    public function __invoke(Request $request): View
    {
        $postsPerPage = (int)(get_setting('posts', 'posts_per_page') ?: 12);
        $page = max(1, (int)$request->query('page', 1));

        $posts = app(GetPostListAction::class)->execute(
            page: $page,
            perPage: $postsPerPage,
        );

        return view('front.posts.list', compact('posts'));
    }
}
