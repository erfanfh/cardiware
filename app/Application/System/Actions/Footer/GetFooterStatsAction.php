<?php

namespace App\Application\System\Actions\Footer;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class GetFooterStatsAction
{
    /**
     * آمار کلی سایت برای نوار هیروباکس فوتر:
     * کاربران، تیکت‌ها، نوشته‌ها و محصولات.
     *
     * @return array{
     *     users:int,
     *     tickets:int,
     *     posts:int,
     *     products:int
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::FOOTER_STATS,
        ])->remember(
            CacheKey::footerStats(),
            CacheTtl::long(),
            function () {
                $activePostsCount = Post::query()
                    ->where('status', PostStatusEnum::ACTIVE)
                    ->count();

                $visibleProductsCount = Product::query()
                    ->where('status', '!=', 'draft')
                    ->count();

                return [
                    'users' => $this->roundDownToHundred(User::query()->count()),
                    'tickets' => $this->roundDownToHundred(Ticket::query()->count()),
                    'posts' => $this->roundDownToHundred($activePostsCount),
                    'products' => $visibleProductsCount,
                ];
            }
        );
    }

    /**
     * گرد کردن به پایین برای اعداد بزرگ (نمایش «+۱۲٬۳۰۰» به‌جای عدد دقیق).
     *
     * مقادیر کوچک نباید صفر شوند: قبلاً با ۵ محصول فعال، خروجی ۰ بود و باکس
     * «در یک نگاه» عدد «+۰» نشان می‌داد.
     */
    protected function roundDownToHundred(int $value): int
    {
        if ($value <= 0) {
            return 0;
        }

        if ($value < 100) {
            return $value;
        }

        return (int)(floor($value / 100) * 100);
    }
}
