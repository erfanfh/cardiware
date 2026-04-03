<?php

namespace App\Domain\Feature\Enums;

enum FrontBadgeOptionEnum: string
{
    case FIFA = 'FIFA';
    case PES = 'PES';
    case ANDROID = 'Android';
    case ATS = 'ATS';
    case ETS = 'ETS';
    case NBA = 'NBA';
    case FIFA_Android = 'FIFA-Android';
    case PES_Android = 'PES-Android';
    case ARTICLE = 'Article';
    case REPORTAGE = 'Reportage';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
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
