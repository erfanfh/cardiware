<?php

namespace App\Domain\Seo\Enums;

enum SeoCheckStatusEnum: string
{
    case PROBLEM = 'problem';
    case IMPROVEMENT = 'improvement';
    case GOOD = 'good';

    public function label(): string
    {
        return match ($this) {
            self::PROBLEM => 'مشکل',
            self::IMPROVEMENT => 'نیاز به بهبود',
            self::GOOD => 'خوب',
        };
    }
}
