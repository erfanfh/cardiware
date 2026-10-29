<?php

namespace App\Domain\Marketplace\Enums;

enum SellerPermissionEnum: string
{
    case ACCESS_PANEL = 'access_panel';
    case VIEW_SALES_COUNT = 'view_sales_count';
    case VIEW_REVENUE = 'view_revenue';
    case VIEW_BUYER_INFO = 'view_buyer_info';
    case VIEW_SALES_DETAILS = 'view_sales_details';
    case VIEW_COMMISSION = 'view_commission';
    case VIEW_COMMENTS = 'view_comments';
    case VIEW_TICKETS = 'view_tickets';
    case REPLY_TICKETS = 'reply_tickets';
    case MANAGE_WITHDRAWALS = 'manage_withdrawals';

    public function label(): string
    {
        return match ($this) {
            self::ACCESS_PANEL => 'ورود به پنل فروشگاه',
            self::VIEW_SALES_COUNT => 'نمایش تعداد فروش و سفارش‌ها',
            self::VIEW_REVENUE => 'نمایش درآمد و مبالغ فروش',
            self::VIEW_BUYER_INFO => 'نمایش اطلاعات خریداران',
            self::VIEW_SALES_DETAILS => 'مشاهده ریز فروش و شماره سفارش‌ها',
            self::VIEW_COMMISSION => 'نمایش درصد کمیسیون و سهم پلتفرم',
            self::VIEW_COMMENTS => 'مشاهده دیدگاه‌های محصولات',
            self::VIEW_TICKETS => 'مشاهده تیکت‌ها و پیوست‌ها',
            self::REPLY_TICKETS => 'پاسخ‌گویی به تیکت‌ها',
            self::MANAGE_WITHDRAWALS => 'مشاهده و ثبت درخواست وجه',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::ACCESS_PANEL => 'با غیرفعال‌سازی، همه فروشندگان این فروشگاه از دسترسی به پنل آن محروم می‌شوند.',
            self::VIEW_SALES_COUNT => 'تعداد فروش محصول، افزونه و سفارش در همه گزارش‌ها. با غیرفعال‌سازی، ریز فروش نیز بسته می‌شود تا شمارش ردیف‌ها تعداد فروش را آشکار نکند.',
            self::VIEW_REVENUE => 'همه مبالغ فروش و درآمد، نمودار درآمد و مانده مالی. با غیرفعال‌سازی، بخش درخواست وجه نیز بسته می‌شود.',
            self::VIEW_BUYER_INFO => 'با غیرفعال‌سازی، دیدگاه‌ها، تیکت‌ها و پیوست‌های آن‌ها نیز بسته می‌شوند؛ این محتوا ممکن است شامل اطلاعات شخصی باشد. شماره سفارش در ریز فروش قابل مشاهده می‌ماند.',
            self::VIEW_SALES_DETAILS => 'دسترسی به جزئیات نسخه‌ها، افزونه‌ها و ردیف سفارش‌ها؛ نیازمند نمایش تعداد فروش است.',
            self::VIEW_COMMISSION => 'درصد سهم فروشگاه، نرخ مؤثر و سهم پلتفرم در گزارش‌ها؛ مبالغ فقط با دسترسی نمایش درآمد نشان داده می‌شوند.',
            self::VIEW_COMMENTS => 'نیازمند فعال بودن نمایش اطلاعات خریداران است.',
            self::VIEW_TICKETS => 'نیازمند فعال بودن نمایش اطلاعات خریداران است.',
            self::REPLY_TICKETS => 'نیازمند دسترسی مشاهده تیکت‌ها است؛ می‌توان تیکت‌ها را فقط خواندنی کرد.',
            self::MANAGE_WITHDRAWALS => 'نیازمند فعال بودن نمایش درآمد است.',
        };
    }

    /** @return list<self> */
    public function dependencies(): array
    {
        return match ($this) {
            self::VIEW_SALES_DETAILS => [self::VIEW_SALES_COUNT],
            self::VIEW_COMMENTS, self::VIEW_TICKETS => [self::VIEW_BUYER_INFO],
            self::REPLY_TICKETS => [self::VIEW_TICKETS],
            self::MANAGE_WITHDRAWALS => [self::VIEW_REVENUE],
            default => [],
        };
    }

    public static function forRoute(?string $route): ?self
    {
        return match ($route) {
            'seller.stores.products.sales' => self::VIEW_SALES_DETAILS,
            'seller.stores.comments' => self::VIEW_COMMENTS,
            'seller.stores.tickets', 'seller.stores.tickets.show' => self::VIEW_TICKETS,
            'seller.stores.tickets.reply' => self::REPLY_TICKETS,
            'seller.stores.withdrawals', 'seller.stores.withdrawals.store' => self::MANAGE_WITHDRAWALS,
            default => null,
        };
    }
}
