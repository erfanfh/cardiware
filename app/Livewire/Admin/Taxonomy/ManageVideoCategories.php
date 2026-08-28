<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageHierarchicalTaxonomyCategories;

class ManageVideoCategories extends ManageHierarchicalTaxonomyCategories
{
    protected function taxonomyKey(): TaxonomyKeyEnum { return TaxonomyKeyEnum::VIDEO_CATEGORY; }
    protected function permissionName(): string { return 'video.category'; }
    protected function countRelationName(): string { return 'videos'; }
    protected function frontRouteName(): string { return 'videos.categories'; }
    protected function viewName(): string { return 'livewire.admin.taxonomy.manage-video-categories'; }
    protected function successEntityName(): string { return 'دسته‌بندی ویدیو'; }
}
