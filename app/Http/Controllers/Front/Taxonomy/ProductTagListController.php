<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class ProductTagListController extends BaseTaxonomyListController
{
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__PRODUCT_TAG;
    }

    protected function routeName(): string
    {
        return 'products.tags';
    }

    protected function paginatedRouteName(): string
    {
        return 'products.tags.page';
    }

    protected function viewName(): string
    {
        return 'front.catalog.products.list';
    }

    protected function termViewVariableName(): string
    {
        return 'tag';
    }
}
