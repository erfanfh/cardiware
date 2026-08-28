<?php

namespace App\Domain\Taxonomy\Enums;

use App\Domain\Slug\Enums\SlugScopeEnum;

enum TaxonomyKeyEnum: string
{
    case POST_CATEGORY = 'post_category';
    case POST_TAG = 'post_tag';
    case PROJECT_TAG = 'project_tag';
    case PRODUCT_CATEGORY = 'product_category';
    case PRODUCT_TAG = 'product_tag';
    case VIDEO_CATEGORY = 'video_category';
    case VIDEO_TAG = 'video_tag';

    public function label(): string
    {
        return match ($this) {
            self::POST_CATEGORY => 'دسته‌بندی نوشته',
            self::POST_TAG => 'برچسب نوشته',
            self::PROJECT_TAG => 'برچسب پروژه',
            self::PRODUCT_CATEGORY => 'دسته‌بندی محصول',
            self::PRODUCT_TAG => 'برچسب محصول',
            self::VIDEO_CATEGORY => 'دسته‌بندی ویدیو',
            self::VIDEO_TAG => 'برچسب ویدیو',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::POST_CATEGORY => 'دسته‌بندی‌های نوشته',
            self::POST_TAG => 'برچسب‌های نوشته',
            self::PROJECT_TAG => 'برچسب‌های پروژه',
            self::PRODUCT_CATEGORY => 'دسته‌بندی‌های محصول',
            self::PRODUCT_TAG => 'برچسب‌های محصول',
            self::VIDEO_CATEGORY => 'دسته‌بندی‌های ویدیو',
            self::VIDEO_TAG => 'برچسب‌های ویدیو',
        };
    }

    public function isHierarchical(): bool
    {
        return match ($this) {
            self::POST_CATEGORY, self::PRODUCT_CATEGORY, self::VIDEO_CATEGORY => true,
            self::POST_TAG, self::PROJECT_TAG, self::PRODUCT_TAG, self::VIDEO_TAG => false,
        };
    }

    public function archiveRouteName(): ?string
    {
        return match ($this) {
            self::POST_CATEGORY => 'posts.categories',
            self::POST_TAG => 'posts.tags',
            self::PROJECT_TAG => null,
            self::PRODUCT_CATEGORY => 'products.categories',
            self::PRODUCT_TAG => 'products.tags',
            self::VIDEO_CATEGORY => 'videos.categories',
            self::VIDEO_TAG => 'videos.tags',
        };
    }

    public function adminTemplate(): TaxonomyAdminTemplateEnum
    {
        return match ($this) {
            self::POST_CATEGORY, self::PRODUCT_CATEGORY, self::VIDEO_CATEGORY => TaxonomyAdminTemplateEnum::HIERARCHICAL,
            self::POST_TAG, self::PROJECT_TAG, self::PRODUCT_TAG, self::VIDEO_TAG => TaxonomyAdminTemplateEnum::FLAT,
        };
    }

    public function slugScope(): SlugScopeEnum
    {
        return SlugScopeEnum::fromTaxonomyKey($this);
    }
}
