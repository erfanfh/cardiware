<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildAggregateRatingSchemaAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context): array
    {
        if ($context->averageRating === null || $context->ratingCount < 1) {
            return [];
        }

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => $context->averageRating,
            'ratingCount' => $context->ratingCount,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
}
