<?php

namespace App\Providers;

use App\Application\Shared\Cache\Observers\PublicReadCacheObserver;
use App\Domain\Career\Models\CareerPosition;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Domain\Notification\Models\AccountAnnouncement;
use App\Domain\Notification\Models\Popup;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\Poll\Models\Poll;
use App\Domain\Poll\Models\PollOption;
use App\Domain\Project\Models\Project;
use App\Domain\Promotion\Models\Coupon;
use App\Domain\Promotion\Models\CouponTarget;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawPrize;
use App\Domain\Raffle\Models\DrawRun;
use App\Domain\Raffle\Models\DrawTicket;
use App\Domain\Raffle\Models\DrawWinner;
use App\Domain\Seo\Models\SeoMeta;
use App\Domain\Story\Models\Story;
use App\Domain\Support\Models\TicketDepartment;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Domain\Video\Models\Video;
use App\Domain\Vip\Models\VipPlan;
use Illuminate\Support\ServiceProvider;

final class PublicReadCacheServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach ([
            Post::class, Product::class, Project::class, Video::class,
            TaxonomyTerm::class, User::class, TeamMember::class, CareerPosition::class,
            VipPlan::class, MediaFile::class, MediaFileVariant::class, SeoMeta::class,
            Store::class, ProductSaleItem::class, ProductSaleItemDiscount::class,
            ProductSaleItemFile::class, Comment::class, PointAccount::class,
            Poll::class, PollOption::class, DrawCampaign::class, DrawPrize::class,
            DrawWinner::class, DrawRun::class, DrawTicket::class, Coupon::class, CouponTarget::class,
            AccountAnnouncement::class, Popup::class, TicketDepartment::class, Story::class,
        ] as $model) {
            $model::observe(PublicReadCacheObserver::class);
        }
    }
}
