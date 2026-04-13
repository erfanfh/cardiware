<?php

namespace App\Domain\Navigation\Enums;

/**
 * Represents the available types of navigation items.
 */
enum NavigationItemTypeEnum: string
{
    case CUSTOM_LINK = 'custom_link';
    case CATEGORY = 'category';
    case TAG = 'tag';
    case POST = 'post';
    case PAGE = 'page';
    case PROJECT = 'project';

    /**
     * Get all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for each item type.
     */
    public function label(): string
    {
        return match ($this) {
            self::CUSTOM_LINK => 'لینک اختصاصی',
            self::CATEGORY => 'دسته‌بندی',
            self::TAG => 'برچسب',
            self::POST => 'نوشته',
            self::PAGE => 'برگه',
            self::PROJECT => 'پروژه',
        };
    }

    /**
     * Get options for selects.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Determine whether the item type is manually linked.
     */
    public function isCustomLink(): bool
    {
        return $this === self::CUSTOM_LINK;
    }

    /**
     * Determine whether the item type should point to a model reference.
     */
    public function requiresReference(): bool
    {
        return !$this->isCustomLink();
    }
}
