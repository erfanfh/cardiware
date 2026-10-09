<?php

namespace App\Application\Seo\DTOs;

use App\Application\Discussion\DTOs\CommentReviewDTO;
use App\Domain\Catalog\Enums\ProductStructureEnum;

final class ProductSchemaContextDTO
{
    /**
     * @param array<int, array<string, mixed>> $offers
     * @param array<int, array<string, mixed>> $additionalProperties
     * @param array<int, string> $keywords
     * @param array<int, CommentReviewDTO> $reviews
     */
    public function __construct(
        public readonly SchemaContextDTO     $base,

        public readonly int                  $productId,
        public readonly string               $productName,
        public readonly ?string              $productDescription,
        public readonly ?string              $sku,

        public readonly ProductStructureEnum $structure,

        public readonly ?string              $categoryName = null,
        public readonly ?string              $categoryUrl = null,

        public readonly ?int                 $sellerId = null,
        public readonly ?string              $sellerName = null,
        public readonly ?string              $sellerUrl = null,
        public readonly ?string              $sellerDescription = null,
        public readonly ?string              $sellerSchemaId = null,
        public readonly string               $sellerType = 'Organization',

        public readonly string               $priceCurrency = 'IRR',
        public readonly ?int                 $price = null,
        public readonly ?int                 $lowPrice = null,
        public readonly ?int                 $highPrice = null,
        public readonly int                  $offerCount = 0,
        public readonly bool                 $isAvailable = false,

        public readonly array                $offers = [],
        public readonly array                $additionalProperties = [],
        public readonly array                $keywords = [],
        public readonly array                $reviews = [],
    )
    {
    }

    public function hasMultipleOffers(): bool
    {
        return $this->structure === ProductStructureEnum::VARIABLE || count($this->offers) > 1;
    }
}
