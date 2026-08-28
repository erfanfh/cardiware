<?php

namespace App\Application\Video\Actions;

use App\Application\Taxonomy\Actions\ResolveTaxonomyTermIdsAction;
use App\Application\Taxonomy\Actions\SyncModelTaxonomyTermsAction;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Video\Models\Video;

final class SyncVideoTaxonomiesAction
{
    public function __construct(
        private readonly ResolveTaxonomyTermIdsAction $resolveIds,
        private readonly SyncModelTaxonomyTermsAction $syncTerms,
    ) {}

    public function execute(Video $video, array $categories, array $tags): void
    {
        $this->syncTerms->execute($video, 'categories', TaxonomyKeyEnum::VIDEO_CATEGORY, $categories);
        $tagIds = $this->resolveIds->execute(TaxonomyKeyEnum::VIDEO_TAG, $tags);
        $this->syncTerms->execute($video, 'tags', TaxonomyKeyEnum::VIDEO_TAG, $tagIds);
    }
}
