<?php

namespace App\Application\Seo\Listeners\Sitemap;

use App\Application\Seo\Actions\Sitemap\MarkSitemapsDirtyAction;

class MarkSitemapsDirtyListener
{
    public function __construct(
        private readonly MarkSitemapsDirtyAction $action,
    )
    {
    }

    public function handle(object $event): void
    {
        $this->action->execute();
    }
}
