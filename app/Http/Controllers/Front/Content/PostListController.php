<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostListAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostListController
{
    public function __invoke(Request $request, ?int $page = null): View|RedirectResponse
    {
        $postsPerPage = (int)(get_setting('posts', 'posts_per_page') ?: 12);

        $page = max(1, (int)($page ?? $request->route('page', 1)));

        if ($page === 1 && $request->routeIs('posts.list.page')) {
            return redirect()->to(front_route('posts.list'), 301);
        }

        $posts = app(GetPostListAction::class)->execute(
            page: $page,
            perPage: $postsPerPage,
        );

        if ($page > 1 && $posts->lastPage() > 0 && $page > $posts->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $posts);
        $request->attributes->set('seo_pagination_base_url', front_route('posts.list'));

        return view('front.posts.list', compact('posts'));
    }
}
