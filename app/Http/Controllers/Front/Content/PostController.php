<?php

namespace App\Http\Controllers\Front\Content;

use App\Application\System\Actions\StoreViewAction;
use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Post detail
     *
     * @param Post $post
     * @return View
     */
    public function detail(Post $post): View
    {
        (new StoreViewAction())->execute($post);

        return view('front.posts.detail', compact('post'));
    }

    /**
     * Post list
     *
     * @return View
     */
    public function list(): View
    {
        return view('front.posts.list');
    }
}
