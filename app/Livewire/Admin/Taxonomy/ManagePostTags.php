<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageFlatTaxonomyTerms;

class ManagePostTags extends ManageFlatTaxonomyTerms
{
    protected function taxonomyKey(): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::POST_TAG;
    }

    protected function permissionName(): string
    {
        return 'tag.manage';
    }

    protected function countRelationName(): string
    {
        return 'posts';
    }

    protected function viewName(): string
    {
        return 'livewire.admin.taxonomy.manage-post-tags';
    }

    protected function frontRouteName(): string
    {
        return 'posts.tags';
    }

    protected function successEntityName(): string
    {
        return 'برچسب نوشته';
    }
}
