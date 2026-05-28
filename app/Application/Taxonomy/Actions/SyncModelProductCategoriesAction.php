<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use Illuminate\Database\Eloquent\Model;

class SyncModelProductCategoriesAction
{
    public function __construct(
        protected SyncModelTaxonomyTermsAction $syncModelTaxonomyTermsAction,
    )
    {
    }

    /**
     * @param Model $model
     * @param array<int, int|string|null> $payload
     * @return void
     */
    public function execute(Model $model, array $payload): void
    {
        $this->syncModelTaxonomyTermsAction->execute(
            model: $model,
            relation: 'categories',
            taxonomy: TaxonomyKeyEnum::PRODUCT_CATEGORY,
            ids: $payload,
        );
    }
}
