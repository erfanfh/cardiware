<?php

namespace App\Application\Seo\Actions\Schema\Contexts;

use App\Application\Discussion\Actions\GetCommentableRatingSummaryAction;
use App\Application\Discussion\Actions\GetCommentableReviewsAction;
use App\Application\Seo\Actions\GetSeoDefaultsAction;
use App\Application\Seo\DTOs\ProductSchemaContextDTO;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Application\Seo\Support\SchemaMoneyFormatter;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use Illuminate\Support\Str;

final class BuildProductSchemaContextAction
{
    public function __construct(
        protected GetSeoDefaultsAction              $getSeoDefaultsAction,
        protected SchemaIdGenerator                 $schemaIdGenerator,
        protected SchemaMoneyFormatter              $schemaMoneyFormatter,
        protected GetCommentableRatingSummaryAction $getCommentableRatingSummaryAction,
        protected GetCommentableReviewsAction       $getCommentableReviewsAction,
    )
    {
    }

    public function execute(Product $product, ResolvedSeoPayloadDTO $payload): ProductSchemaContextDTO
    {
        $defaults = $this->getSeoDefaultsAction->execute();
        $ratingSummary = $this->getCommentableRatingSummaryAction->execute($product);

        $siteName = $defaults['site_name'] ?? null;
        $siteUrl = $defaults['site_url'] ?? null;
        $logo = $defaults['organization_logo'] ?? null;

        $base = new SchemaContextDTO(
            pageUrl: $payload->canonicalUrl,
            pageTitle: $payload->title,
            pageDescription: $payload->description,
            pageType: 'ItemPage',

            siteUrl: (string)$siteUrl,
            siteName: (string)$siteName,
            siteAlternateName: get_setting('seo', 'site_alternate_name'),
            siteDescription: get_setting('seo', 'site_description'),
            inLanguage: (string)get_setting('seo', 'schema_language', 'fa-IR'),

            organizationName: get_setting('seo', 'organization_name', get_setting('seo', 'site_name', config('app.name'))),
            organizationAlternateName: get_setting('seo', 'organization_alternate_name'),
            organizationLegalName: get_setting('seo', 'organization_legal_name'),
            organizationEmail: get_setting('seo', 'organization_email'),
            organizationTelephone: get_setting('seo', 'organization_phone'),
            organizationFoundingDate: get_setting('seo', 'organization_founding_date'),
            organizationSameAs: $this->normalizeSameAs(
                get_setting('seo', 'organization_same_as', [])
            ),
            organizationNumberOfEmployees: get_setting('seo', 'organization_number_of_employees'),

            logoUrl: $logo?->url,
            logoWidth: $logo?->primaryVariant?->width,
            logoHeight: $logo?->primaryVariant?->height,
            logoCaption: $siteName,

            pageImageUrl: $payload->pageImageUrl,
            pageImageWidth: $payload->pageImageWidth,
            pageImageHeight: $payload->pageImageHeight,
            pageImageCaption: $payload->pageImageCaption,
            pageImageSchemaId: $payload->pageImageUrl
                ? $this->schemaIdGenerator->primaryImage($payload->canonicalUrl)
                : null,

            headline: $payload->headline ?: $product->title,
            publishedTime: $payload->publishedTime ?: $product->published_at?->toAtomString(),
            modifiedTime: $payload->modifiedTime ?: $product->updated_at?->toAtomString(),
            keywords: $payload->keywords,

            averageRating: $ratingSummary->averageRating,
            ratingCount: $ratingSummary->ratingCount,
        );

        $primaryCategory = $product->categories->first();

        $availableSaleItems = $product->saleItems
            ->filter(fn(ProductSaleItem $saleItem): bool => $saleItem->status === ProductSaleItemStatusEnum::AVAILABLE)
            ->values();

        $allSaleItems = $product->saleItems->values();

        $prices = $availableSaleItems
            ->pluck('base_price')
            ->filter(fn($price): bool => $price !== null)
            ->map(fn($price): int => $this->schemaMoneyFormatter->tomanToIrr((int)$price))
            ->values();

        $sellerName = $product->store?->title ?: $base->organizationName;
        $sellerUrl = filled($product->store?->slug)
            ? front_route('stores.detail', $product->store->slug)
            : $base->siteUrl;

        $sellerSchemaId = $product->store
            ? $this->schemaIdGenerator->store($base->siteUrl, $product->store->id)
            : $this->schemaIdGenerator->organization($base->siteUrl);

        $offers = $allSaleItems
            ->map(fn(ProductSaleItem $saleItem): array => $this->buildSaleItemOffer(
                product: $product,
                saleItem: $saleItem,
                pageUrl: $base->pageUrl,
                sellerSchemaId: $sellerSchemaId,
            ))
            ->values()
            ->all();

        return new ProductSchemaContextDTO(
            base: $base,

            productId: $product->id,
            productName: $product->title,
            productDescription: $payload->description ?: $product->excerpt ?: Str::limit(strip_tags((string)$product->body), 300),
            sku: $product->sku,

            structure: $product->structure,

            categoryName: $primaryCategory?->title,
            categoryUrl: $primaryCategory
                ? front_route($primaryCategory->taxonomy->archiveRouteName(), $primaryCategory->path)
                : null,

            sellerId: $product->store?->id,
            sellerName: $sellerName,
            sellerUrl: $sellerUrl,
            sellerDescription: $product->store?->body,
            sellerSchemaId: $sellerSchemaId,
            sellerType: 'Organization',

            priceCurrency: $this->schemaMoneyFormatter->currency(),
            price: $prices->min(),
            lowPrice: $prices->min(),
            highPrice: $prices->max(),

            offerCount: $allSaleItems->count(),
            isAvailable: $availableSaleItems->isNotEmpty(),

            offers: $offers,
            additionalProperties: $this->buildAdditionalProperties($product),
            keywords: $product->tags->pluck('title')->values()->all(),
            reviews: $this->getCommentableReviewsAction->execute($product),
        );
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    protected function buildSaleItemOffer(
        Product         $product,
        ProductSaleItem $saleItem,
        string          $pageUrl,
        ?string         $sellerSchemaId,
    ): array
    {
        return [
            '@type' => 'Offer',
            '@id' => $this->schemaIdGenerator->offer($pageUrl, $saleItem->id),
            'url' => $pageUrl,
            'sku' => $saleItem->sku ?: $product->sku,
            'priceCurrency' => $this->schemaMoneyFormatter->currency(),
            'price' => $this->schemaMoneyFormatter->tomanToIrr((int)$saleItem->base_price),
            'availability' => $saleItem->status === ProductSaleItemStatusEnum::AVAILABLE
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => $sellerSchemaId ? [
                '@id' => $sellerSchemaId,
            ] : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildAdditionalProperties(Product $product): array
    {
        return array_values(array_filter([
            [
                '@type' => 'PropertyValue',
                'name' => 'نوع محصول',
                'value' => $product->type?->label(),
            ],
            [
                '@type' => 'PropertyValue',
                'name' => 'ساختار محصول',
                'value' => $product->structure?->label(),
            ],
        ], fn(array $item): bool => filled($item['value'] ?? null)));
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeSameAs(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/\r\n|\r|\n/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn($item) => is_string($item) ? trim($item) : null,
            $value
        )));
    }
}
