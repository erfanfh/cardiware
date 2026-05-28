<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageFlatTaxonomyTerms;

class ManageProductTags extends ManageFlatTaxonomyTerms
{
    protected function taxonomyKey(): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::PRODUCT_TAG;
    }

    protected function permissionName(): string
    {
        return 'product.tag';
    }

    protected function countRelationName(): string
    {
        return 'products';
    }

    protected function viewName(): string
    {
        return 'livewire.admin.taxonomy.manage-product-tags';
    }

    protected function frontRouteName(): string
    {
        return 'products.tags';
    }

    protected function successEntityName(): string
    {
        return 'برچسب محصول';
    }
}
