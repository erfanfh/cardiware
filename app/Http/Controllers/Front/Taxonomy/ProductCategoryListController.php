<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Domain\Slug\Enums\SlugScopeEnum;

class ProductCategoryListController extends BaseTaxonomyListController
{
    protected function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::TAXONOMY__PRODUCT_CATEGORY;
    }

    protected function routeName(): string
    {
        return 'products.categories';
    }

    protected function paginatedRouteName(): string
    {
        return 'products.categories.page';
    }

    protected function viewName(): string
    {
        return 'front.catalog.products.list';
    }

    protected function termViewVariableName(): string
    {
        return 'category';
    }
}
