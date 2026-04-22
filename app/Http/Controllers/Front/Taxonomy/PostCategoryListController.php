<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class PostCategoryListController extends BaseTaxonomyListController
{
    /**
     * @return SlugScopeEnum
     */
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__POST_CATEGORY;
    }

    /**
     * @return string
     */
    protected function termViewVariableName(): string
    {
        return 'category';
    }
}
