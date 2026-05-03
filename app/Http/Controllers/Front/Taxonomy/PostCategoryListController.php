<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class PostCategoryListController extends BaseTaxonomyListController
{
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__POST_CATEGORY;
    }

    protected function routeName(): string
    {
        return 'posts.categories';
    }

    protected function paginatedRouteName(): string
    {
        return 'posts.categories.page';
    }

    protected function termViewVariableName(): string
    {
        return 'category';
    }
}
