<?php

namespace App\Domain\Seo\Enums;

enum SeoTemplateVariableEnum: string
{
    case SITE_TITLE = 'site_title';
    case SEPARATOR = 'separator';
    case POST_TITLE = 'post_title';
    case PAGE_TITLE = 'page_title';
    case PROJECT_TITLE = 'project_title';
    case TAXONOMY_TITLE = 'taxonomy_title';
    case AUTHOR_NAME = 'author_name';
    case SEARCH_TERM = 'search_term';
    case PRODUCT_TITLE = 'product_title';
    case STORE_TITLE = 'store_title';
    case VIDEO_TITLE = 'video_title';

    public function token(): string
    {
        return '{' . $this->value . '}';
    }

    public function label(): string
    {
        return match ($this) {
            self::SITE_TITLE => 'نام سایت',
            self::SEPARATOR => 'جداکننده',
            self::POST_TITLE => 'عنوان نوشته',
            self::PAGE_TITLE => 'عنوان برگه',
            self::PROJECT_TITLE => 'عنوان پروژه',
            self::TAXONOMY_TITLE => 'عنوان',
            self::AUTHOR_NAME => 'نام نویسنده',
            self::SEARCH_TERM => 'عبارت جستجو شده',
            self::PRODUCT_TITLE => 'عنوان محصول',
            self::STORE_TITLE => 'عنوان فروشنده',
            self::VIDEO_TITLE => 'عنوان ویدیو',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SITE_TITLE => 'نام اصلی سایت',
            self::SEPARATOR => 'جداکننده عنوان سئو',
            self::POST_TITLE => 'عنوان نوشته',
            self::PAGE_TITLE => 'عنوان برگه',
            self::PROJECT_TITLE => 'عنوان پروژه',
            self::TAXONOMY_TITLE => 'عنوان',
            self::AUTHOR_NAME => 'نام نویسنده',
            self::SEARCH_TERM => 'عبارت جستجو شده',
            self::PRODUCT_TITLE => 'عنوان محصول',
            self::STORE_TITLE => 'عنوان فروشنده',
            self::VIDEO_TITLE => 'عنوان ویدیو',
        };
    }

    public function isAvailableFor(string $context): bool
    {
        return match ($context) {
            'post' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::POST_TITLE,
            ], true),

            'page' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::PAGE_TITLE,
            ], true),

            'project' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::PROJECT_TITLE,
            ], true),

            'taxonomy' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::TAXONOMY_TITLE,
            ], true),

            'search' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::SEARCH_TERM,
            ], true),

            'product' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::PRODUCT_TITLE,
            ], true),

            'store' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::STORE_TITLE,
            ], true),

            'video' => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
                self::VIDEO_TITLE,
            ], true),

            default => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
            ], true),
        };
    }
}
