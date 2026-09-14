<?php

namespace Tests\Unit\Seo;

use App\Application\Seo\Actions\Schema\BuildAggregateRatingSchemaAction;
use App\Application\Seo\Actions\Schema\BuildPostArticleSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductAggregateOfferSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductOfferSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductSchemaAction;
use App\Application\Seo\DTOs\ProductSchemaContextDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use PHPUnit\Framework\TestCase;

final class AggregateRatingSchemaTest extends TestCase
{
    public function test_it_builds_a_google_compatible_aggregate_rating(): void
    {
        $context = $this->baseContext(averageRating: 4.33, ratingCount: 12);

        $schema = (new BuildAggregateRatingSchemaAction)->execute($context);

        self::assertSame([
            '@type' => 'AggregateRating',
            'ratingValue' => 4.33,
            'ratingCount' => 12,
            'bestRating' => 5,
            'worstRating' => 1,
        ], $schema);
    }

    public function test_it_omits_an_aggregate_rating_when_there_are_no_rated_reviews(): void
    {
        $schema = (new BuildAggregateRatingSchemaAction)->execute($this->baseContext());

        self::assertSame([], $schema);
    }

    public function test_it_nests_the_aggregate_rating_in_the_article_node(): void
    {
        $aggregateRatingBuilder = new BuildAggregateRatingSchemaAction;
        $builder = new BuildPostArticleSchemaAction(
            new SchemaIdGenerator,
            $aggregateRatingBuilder,
        );

        $schema = $builder->execute(
            $this->baseContext(averageRating: 4.5, ratingCount: 8),
        );

        self::assertSame('Article', $schema['@type']);
        self::assertSame(4.5, $schema['aggregateRating']['ratingValue']);
        self::assertSame(8, $schema['aggregateRating']['ratingCount']);
    }

    public function test_it_nests_the_aggregate_rating_in_the_product_node(): void
    {
        $aggregateRatingBuilder = new BuildAggregateRatingSchemaAction;
        $builder = new BuildProductSchemaAction(
            new BuildProductOfferSchemaAction,
            new BuildProductAggregateOfferSchemaAction,
            $aggregateRatingBuilder,
        );

        $context = new ProductSchemaContextDTO(
            base: $this->baseContext(averageRating: 3.75, ratingCount: 4),
            productId: 10,
            productName: 'محصول تست',
            productDescription: 'توضیحات محصول تست',
            sku: 'TEST-10',
            structure: ProductStructureEnum::SIMPLE,
        );

        $schema = $builder->execute($context);

        self::assertSame('Product', $schema['@type']);
        self::assertSame(3.75, $schema['aggregateRating']['ratingValue']);
        self::assertSame(4, $schema['aggregateRating']['ratingCount']);
    }

    private function baseContext(
        ?float $averageRating = null,
        int $ratingCount = 0,
    ): SchemaContextDTO {
        return new SchemaContextDTO(
            pageUrl: 'https://example.com/example',
            pageTitle: 'صفحه تست',
            averageRating: $averageRating,
            ratingCount: $ratingCount,
        );
    }
}
