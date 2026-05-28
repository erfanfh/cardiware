<?php

namespace App\Http\Controllers\Admin\Taxonomy;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Admin\AdminSearchController;
use Illuminate\Database\Eloquent\Builder;

class PostTagSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return TaxonomyTerm::class;
    }

    protected function permissionName(): string
    {
        return 'tag.manage';
    }

    protected function baseQuery(): Builder
    {
        return parent::baseQuery()
            ->where('taxonomy', TaxonomyKeyEnum::POST_TAG);
    }
}
