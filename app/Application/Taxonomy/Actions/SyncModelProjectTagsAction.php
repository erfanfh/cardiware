<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SyncModelProjectTagsAction
{
    public function __construct(
        protected ResolveTaxonomyTermIdsAction $resolveTaxonomyTermIdsAction,
    )
    {
    }

    /**
     * @param Model $model
     * @param array<string, mixed>|null $payload
     * @return void
     * @throws Throwable
     */
    public function execute(Model $model, ?array $payload): void
    {
        if (!method_exists($model, 'tags')) {
            return;
        }

        $tagIds = $this->resolveTaxonomyTermIdsAction->execute(
            TaxonomyKeyEnum::PROJECT_TAG,
            $payload ?? [],
        );

        $model->tags()->sync($tagIds);
    }
}
