<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class PostTagListController extends BaseTaxonomyListController
{
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__POST_TAG;
    }

    protected function routeName(): string
    {
        return 'posts.tags';
    }

    protected function paginatedRouteName(): string
    {
        return 'posts.tags.page';
    }

    protected function termViewVariableName(): string
    {
        return 'tag';
    }
}
