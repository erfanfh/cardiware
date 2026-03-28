<?php

namespace App\Application\Taxonomy\Actions;

use App\Application\Slug\Actions\AssignSlugAction;
use App\Application\Slug\DTOs\AssignSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Enums\TaxonomyTermTypeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class UpdateTagAction
{
    public function __construct(
        protected AssignSlugAction $assignSlugAction,
    )
    {
    }

    /**
     * @param TaxonomyTerm $tag
     * @param array $data
     * @return TaxonomyTerm
     * @throws Throwable
     */
    public function execute(TaxonomyTerm $tag, array $data): TaxonomyTerm
    {
        return DB::transaction(function () use ($tag, $data) {
            if ($tag->type !== TaxonomyTermTypeEnum::TAG)
                throw new InvalidArgumentException('Tag type should be "' . TaxonomyTermTypeEnum::TAG->value . '" but "' . $tag->type->value . '" given.');

            $tagData = $data;

            unset(
                $tagData['slug'],
                $tagData['type'],
            );

            $tag->update($tagData);

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
