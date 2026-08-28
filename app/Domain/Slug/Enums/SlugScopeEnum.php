<?php

namespace App\Domain\Slug\Enums;

use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use InvalidArgumentException;

enum SlugScopeEnum: string
{
    case CONTENT = 'content';
    case PROJECT = 'project';
    case PRODUCT = 'product';
    case RAFFLE = 'raffle';
    case POLL = 'poll';
    case STORE = 'store';
    case VIDEO = 'video';

    case TAXONOMY__POST_CATEGORY = 'taxonomy:post_category';
    case TAXONOMY__POST_TAG = 'taxonomy:post_tag';
    case TAXONOMY__PROJECT_TAG = 'taxonomy:project_tag';
    case TAXONOMY__PRODUCT_CATEGORY = 'taxonomy:product_category';
    case TAXONOMY__PRODUCT_TAG = 'taxonomy:product_tag';
    case TAXONOMY__VIDEO_CATEGORY = 'taxonomy:video_category';
    case TAXONOMY__VIDEO_TAG = 'taxonomy:video_tag';

    /**
     * Determine whether the given scope belongs to taxonomy scopes.
     */
    public static function isTaxonomyScope(self $scope): bool
    {
        return str_starts_with($scope->value, 'taxonomy:');
    }

    /**
     * Extract taxonomy key string from a taxonomy scope.
     */
    public static function extractTaxonomyKey(self $scope): string
    {
        if (! self::isTaxonomyScope($scope)) {
            throw new InvalidArgumentException("Scope [{$scope->value}] is not a taxonomy scope.");
        }

        return trim(explode(':', $scope->value, 2)[1] ?? '');
    }

    /**
     * Resolve taxonomy enum from a taxonomy scope.
     */
    public static function extractTaxonomyEnum(self $scope): TaxonomyKeyEnum
    {
        return TaxonomyKeyEnum::from(self::extractTaxonomyKey($scope));
    }

    /**
     * Build a slug scope enum from taxonomy key enum.
     */
    public static function fromTaxonomyKey(TaxonomyKeyEnum $taxonomy): self
    {
        return match ($taxonomy) {
            TaxonomyKeyEnum::POST_CATEGORY => self::TAXONOMY__POST_CATEGORY,
            TaxonomyKeyEnum::POST_TAG => self::TAXONOMY__POST_TAG,
            TaxonomyKeyEnum::PROJECT_TAG => self::TAXONOMY__PROJECT_TAG,
            TaxonomyKeyEnum::PRODUCT_CATEGORY => self::TAXONOMY__PRODUCT_CATEGORY,
            TaxonomyKeyEnum::PRODUCT_TAG => self::TAXONOMY__PRODUCT_TAG,
            TaxonomyKeyEnum::VIDEO_CATEGORY => self::TAXONOMY__VIDEO_CATEGORY,
            TaxonomyKeyEnum::VIDEO_TAG => self::TAXONOMY__VIDEO_TAG,
        };
    }
}
