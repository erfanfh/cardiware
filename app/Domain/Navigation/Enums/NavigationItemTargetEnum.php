<?php

namespace App\Domain\Navigation\Enums;

/**
 * Represents the available HTML targets for navigation items.
 */
enum NavigationItemTargetEnum: string
{
    case SELF = '_self';
    case BLANK = '_blank';

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
     * Get label for each target.
     */
    public function label(): string
    {
        return match ($this) {
            self::SELF => 'همین صفحه',
            self::BLANK => 'تب جدید',
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
}
