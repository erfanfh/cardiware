<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\Layout\Actions\GetPageLayoutRenderDataAction;
use App\Domain\Content\Models\Post;
use App\Domain\Layout\Models\PageLayout;

class PostListController
{
    public function __construct(
        private readonly GetPageLayoutRenderDataAction $getLayoutRenderDataAction,

    )
    {
    }
    public function __invoke()
    {
        $postsPerPage = get_setting('posts', 'posts_per_page') ?: 12;
        $posts = Post::latest()->paginate($postsPerPage);
        $sidebarLayout = PageLayout::where('key', 'sidebar')->first();
        $sidebarLayoutData = $sidebarLayout ? $this->getLayoutRenderDataAction->execute($sidebarLayout) : null;
        return view('front.posts.list', compact('posts', 'sidebarLayoutData'));
    }
}
