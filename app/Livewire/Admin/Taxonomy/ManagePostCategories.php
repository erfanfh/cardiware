<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageHierarchicalTaxonomyCategories;

class ManagePostCategories extends ManageHierarchicalTaxonomyCategories
{
    protected function taxonomyKey(): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::POST_CATEGORY;
    }

    protected function permissionName(): string
    {
        return 'category.manage';
    }

    protected function countRelationName(): string
    {
        return 'posts';
    }

    protected function frontRouteName(): string
    {
        return 'posts.categories';
    }

    protected function viewName(): string
    {
        return 'livewire.admin.taxonomy.manage-post-categories';
    }

    protected function successEntityName(): string
    {
        return 'دسته‌بندی نوشته';
    }
}
