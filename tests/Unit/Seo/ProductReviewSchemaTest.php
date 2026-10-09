<?php

namespace Tests\Unit\Seo;

use App\Application\Discussion\DTOs\CommentReviewDTO;
use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\BuildAggregateRatingSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductAggregateOfferSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductOfferSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductSchemaAction;
use App\Application\Seo\Actions\Schema\BuildReviewSchemaAction;
use App\Application\Seo\Actions\Schema\RenderSchemaGraphAction;
use App\Application\Seo\DTOs\ProductSchemaContextDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Application\Seo\Support\SchemaSanitizer;
use App\Domain\Catalog\Enums\ProductStructureEnum;
use PHPUnit\Framework\TestCase;

final class ProductReviewSchemaTest extends TestCase
{
    public function test_reviews_are_nested_in_the_product_without_changing_ratings_or_offers(): void
    {
        foreach ([ProductStructureEnum::SIMPLE, ProductStructureEnum::VARIABLE] as $structure) {
            $withoutReviews = $this->productNode($structure);
            $withReviews = $this->productNode($structure, [new CommentReviewDTO(
                id: 123,
                authorName: 'علی رضایی',
                body: 'محصول خوبی بود.',
                rating: 4.0,
                publishedAt: '2026-10-09T10:00:00+00:00',
            )]);

            self::assertSame([
                '@type' => 'Review',
                '@id' => 'https://example.com/product#review-123',
                'author' => ['@type' => 'Person', 'name' => 'علی رضایی'],
                'datePublished' => '2026-10-09T10:00:00+00:00',
                'reviewBody' => 'محصول خوبی بود.',
                'reviewRating' => [
                    '@type' => 'Rating', 'ratingValue' => 4.0,
                    'bestRating' => 5, 'worstRating' => 1,
                ],
            ], $withReviews['review'][0]);
            unset($withReviews['review'], $withoutReviews['review']);
            self::assertSame($withoutReviews, $withReviews);
        }
    }

    public function test_empty_reviews_are_omitted_from_the_final_graph(): void
    {
        $graph = (new AssembleSchemaGraphAction(new SchemaSanitizer))->execute(
            [], [], [], [], [$this->productNode(ProductStructureEnum::SIMPLE)],
        );

        self::assertArrayNotHasKey('review', $graph['@graph'][0]);
        self::assertArrayNotHasKey('reviews', $graph['@graph'][0]);
        self::assertSame(4.5, $graph['@graph'][0]['aggregateRating']['ratingValue']);
        self::assertSame(8, $graph['@graph'][0]['aggregateRating']['ratingCount']);
    }

    public function test_multiple_reviews_and_script_text_survive_safe_json_rendering(): void
    {
        $body = '</script><script>alert("test")</script>';
        $reviews = [
            new CommentReviewDTO(1, 'نویسنده اول', $body, 1.0, null),
            new CommentReviewDTO(2, 'نویسنده دوم', 'عالی', 5.0, null),
        ];
        $graph = (new AssembleSchemaGraphAction(new SchemaSanitizer))->execute(
            [], [], [], [], [$this->productNode(ProductStructureEnum::SIMPLE, $reviews)],
        );
        $json = (new RenderSchemaGraphAction)->execute($graph);

        self::assertStringNotContainsString('</script>', $json);
        $product = json_decode($json, true, flags: JSON_THROW_ON_ERROR)['@graph'][0];
        self::assertCount(2, $product['review']);
        self::assertSame($body, $product['review'][0]['reviewBody']);
        self::assertArrayNotHasKey('datePublished', $product['review'][0]);
    }

    private function productNode(ProductStructureEnum $structure, array $reviews = []): array
    {
        return (new BuildProductSchemaAction(
            new BuildProductOfferSchemaAction,
            new BuildProductAggregateOfferSchemaAction,
            new BuildAggregateRatingSchemaAction,
            new BuildReviewSchemaAction(new SchemaIdGenerator),
        ))->execute(new ProductSchemaContextDTO(
            base: new SchemaContextDTO(
                pageUrl: 'https://example.com/product/', pageTitle: 'محصول',
                averageRating: 4.5, ratingCount: 8,
            ),
            productId: 10, productName: 'محصول', productDescription: 'توضیحات',
            sku: 'PRODUCT-10', structure: $structure, price: 100000,
            lowPrice: 100000, highPrice: 200000, offerCount: 2,
            reviews: $reviews,
        ));
    }
}
