<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SyncModelPostTagsAction
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
            TaxonomyKeyEnum::POST_TAG,
            $payload ?? [],
        );

        app(SyncModelTaxonomyTermsAction::class)->execute($model, 'tags', TaxonomyKeyEnum::POST_TAG, $tagIds);
    }
}
