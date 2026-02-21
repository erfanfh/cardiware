<?php

namespace App\Application\Content\Observers;


use App\Domain\Content\Models\ContentSlug;
use App\Domain\Content\Models\Page;

class PageObserver
{
    /**
     * Handle the Post "created" event.
     */
    public function created(Page $page): void
    {
        $this->storeSlug($page);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Page $page): void
    {

        if ($page->wasChanged('slug')) {
            $page->slugs()->where('is_current', true)->first()->update([
                'is_current' => false
            ]);
            $this->storeSlug($page);
        }
    }

    /**
     * @param Page $page
     * @return void
     */
    protected function storeSlug(Page $page): void
    {
        $page->slugs()->create([
            'slug' => $page->slug,
            'is_current' => true
        ]);
    }
}
