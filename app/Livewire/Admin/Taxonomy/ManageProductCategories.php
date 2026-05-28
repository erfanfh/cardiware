<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageHierarchicalTaxonomyCategories;

class ManageProductCategories extends ManageHierarchicalTaxonomyCategories
{
    protected function taxonomyKey(): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::PRODUCT_CATEGORY;
    }

    protected function permissionName(): string
    {
        return 'product.category';
    }

    protected function countRelationName(): string
    {
        return 'products';
    }

    protected function frontRouteName(): string
    {
        return 'products.categories';
    }

    protected function viewName(): string
    {
        return 'livewire.admin.taxonomy.manage-product-categories';
    }

    protected function successEntityName(): string
    {
        return 'دسته‌بندی محصول';
    }
}
