<?php

namespace App\Http\Controllers\Front\Content;

use App\Domain\Content\Models\Post;
use Illuminate\Http\RedirectResponse;

class QuickPostDetailController
{
    public function __invoke(int $id): RedirectResponse
    {
        $post = Post::findOrFail($id);

        return redirect()->route('contents.detail', $post->slug);
    }
}
