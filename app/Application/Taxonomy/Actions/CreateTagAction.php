<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateTagAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {

    }

    /**
     * Create a new tag and assign its slug.
     *
     * @param array $data
     * @return TaxonomyTerm
     * @throws Throwable
     */
    public function execute(array $data): TaxonomyTerm
    {
        return DB::transaction(function () use ($data) {
            $tagData = $data;

            unset($tagData['slug']);

            $tagData['type'] = TaxonomyTermTypeEnum::TAG;

            $tag = TaxonomyTerm::create($tagData);

            $desiredSlug = trim((string)(($data['slug'] ?: $data['title']) ?? ''));

            $this->assignSlugAction->execute(
                new AssignSlugDTO(
                    scope: SlugScopeEnum::TAXONOMY__TAG,
                    model: $tag,
                    desiredSlug: $desiredSlug,
                )
            );

            return $tag;
        });
    }
}
