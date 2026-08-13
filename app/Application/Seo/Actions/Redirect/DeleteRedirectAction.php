<?php

namespace App\Application\Seo\Actions\Redirect;

use App\Application\Shared\Cache\Invalidators\SeoRedirectCacheInvalidator;
use App\Domain\Seo\Models\Redirect;

class DeleteRedirectAction
{
    public function __construct(
        private readonly SeoRedirectCacheInvalidator $cacheInvalidator,
    ) {
    }

    public function execute(Redirect $redirect): void
    {
        $redirect->delete();

        $this->cacheInvalidator->invalidate();
    }
}
