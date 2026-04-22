<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class PostTagListController extends BaseTaxonomyListController
{
    /**
     * @return SlugScopeEnum
     */
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__POST_TAG;
    }

    /**
     * @return string
     */
    protected function termViewVariableName(): string
    {
        return 'tag';
    }
}
