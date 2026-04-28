<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Illuminate\View\View;

class AuthorController
{
    public function __invoke(User $user): View
    {
        $author = $user;
        $postsPerPage = get_setting('posts', 'posts_per_page') ?: 12;
        $posts = Post::query()
            ->with([
                'picture.primaryVariant',
                'categories'
            ])
            ->where('author_id', $author->id)
            ->latest()
            ->paginate($postsPerPage);

        return view('front.posts.list', compact('posts', 'author'));
    }
}
