<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Discussion\DTOs\CommentReviewDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildReviewSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(CommentReviewDTO $review, string $pageUrl): array
    {
        return [
            '@type' => 'Review',
            '@id' => $this->schemaIdGenerator->custom($pageUrl, '#review-'.$review->id),
            'author' => [
                '@type' => 'Person',
                'name' => $review->authorName,
            ],
            'datePublished' => $review->publishedAt,
            'reviewBody' => $review->body,
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $review->rating,
                'bestRating' => 5,
                'worstRating' => 1,
            ],
        ];
    }
}
