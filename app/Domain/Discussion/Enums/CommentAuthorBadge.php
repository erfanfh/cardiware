<?php

namespace App\Domain\Discussion\Enums;

enum CommentAuthorBadge: string
{
    case ADMIN = 'admin';
    case POST_AUTHOR = 'post_author';
    case PRODUCT_SELLER = 'product_seller';
    case VERIFIED_BUYER = 'verified_buyer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'مدیر',
            self::POST_AUTHOR => 'نویسنده',
            self::PRODUCT_SELLER => 'فروشنده',
            self::VERIFIED_BUYER => 'خریدار',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'مدیر سایت',
            self::POST_AUTHOR => 'نویسنده این نوشته',
            self::PRODUCT_SELLER => 'فروشنده این محصول',
            self::VERIFIED_BUYER => 'خریدار تاییدشده این محصول',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::ADMIN => 100,
            self::POST_AUTHOR => 90,
            self::PRODUCT_SELLER => 90,
            self::VERIFIED_BUYER => 70,
        };
    }
}
