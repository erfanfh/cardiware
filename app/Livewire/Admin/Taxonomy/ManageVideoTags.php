<?php

namespace App\Livewire\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Livewire\Admin\Taxonomy\Base\ManageFlatTaxonomyTerms;

class ManageVideoTags extends ManageFlatTaxonomyTerms
{
    protected function taxonomyKey(): TaxonomyKeyEnum { return TaxonomyKeyEnum::VIDEO_TAG; }
    protected function permissionName(): string { return 'video.tag'; }
    protected function countRelationName(): string { return 'videos'; }
    protected function frontRouteName(): string { return 'videos.tags'; }
    protected function viewName(): string { return 'livewire.admin.taxonomy.manage-video-tags'; }
    protected function successEntityName(): string { return 'برچسب ویدیو'; }
}
