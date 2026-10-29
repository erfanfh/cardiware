<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Content\Actions\GetPostArchiveAction;
use App\Domain\Identity\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthorController
{
    public function __invoke(User $user, ?int $page = null): View|RedirectResponse
    {
        $author = $user;
        $page = max(1, (int) ($page ?? request()->route('page', 1)));

        if ($page === 1 && request()->routeIs('author.page')) {
            return redirect()->route('author', [
                'user' => $author->username,
            ], 301);
        }

        $postsPerPage = get_setting('general', 'author_posts_count_settings') ?? 9;

        $posts = app(GetPostArchiveAction::class)->execute($page, (int) $postsPerPage, $author->id);

        if ($page > 1 && $posts->lastPage() > 0 && $page > $posts->lastPage()) {
            abort(404);
        }

        request()->attributes->set('seo_paginator', $posts);
        request()->attributes->set('seo_pagination_base_url', front_route('author', [
            'user' => $author->username,
        ]));

        return view('front.posts.list', compact('posts', 'author'));
    }
}
