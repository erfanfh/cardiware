<?php

namespace App\Domain\Seo\Enums;

enum SeoTemplateVariableEnum: string
{
    case SITE_TITLE = 'site_title';
    case SEPARATOR = 'separator';
    case POST_TITLE = 'post_title';

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
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SITE_TITLE => 'نام اصلی سایت',
            self::SEPARATOR => 'جداکننده عنوان سئو',
            self::POST_TITLE => 'عنوان نوشته',
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

            default => in_array($this, [
                self::SITE_TITLE,
                self::SEPARATOR,
            ], true),
        };
    }
}
