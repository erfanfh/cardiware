<?php

namespace App\Application\Content\Observers;


use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Post;

class PostObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        $this->storeSlug($post);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {

        if ($post->wasChanged('slug')) {
            $post->slugs()->where('is_current', true)->first()->update([
                'is_current' => false
            ]);
            $this->storeSlug($post);
        }
    }

    /**
     * @param Post $post
     * @return void
     */
    protected function storeSlug(Post $post): void
    {
        $post->slugs()->create([
            'slug' => $post->slug,
            'is_current' => true
        ]);
    }
}
