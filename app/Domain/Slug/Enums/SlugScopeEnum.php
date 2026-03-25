<?php

namespace App\Domain\Slug\Enums;

enum SlugScopeEnum: string
{
    case CONTENT = 'content';
    case PROJECT = 'project';
    case TAXONOMY__TAG = 'taxonomy:tag';
    case TAXONOMY__CATEGORY = 'taxonomy:category';

    /**
     * Determine whether the given scope string belongs to taxonomy scopes.
     */
    public static function isTaxonomyScope(self $scope): bool
    {
        return str_starts_with($scope->value, 'taxonomy:');
    }

    /**
     * Extract taxonomy type from a taxonomy scope string.
     */
    public static function extractTaxonomyType(self $scope): string
    {
        return trim(explode(':', $scope->value, 2)[1] ?? '');
    }
}
