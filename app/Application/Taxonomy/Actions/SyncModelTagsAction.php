<?php

namespace App\Application\Taxonomy\Actions;

use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class SyncModelTagsAction
{
    public function __construct(
        protected ResolveTagIdsAction $resolveTagIdsAction
    )
    {
    }

    /**
     * @param Model $model
     * @param array|null $payload
     * @return void
     * @throws Throwable
     */
    public function execute(Model $model, ?array $payload): void
    {
        if (!method_exists($model, 'tags')) {
            return;
        }

        $tagIds = $this->resolveTagIdsAction->execute($payload ?? []);

        $syncData = collect($tagIds)
            ->mapWithKeys(fn(int $id) => [
                $id => ['type' => TaxonomyTermTypeEnum::TAG->value],
            ])
            ->all();

        $model->tags()->sync($syncData);
    }
}
