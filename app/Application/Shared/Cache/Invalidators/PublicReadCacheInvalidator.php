<?php

namespace App\Application\Shared\Cache\Invalidators;

use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemDiscount;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PublicReadCacheInvalidator
{
    public function forModel(Model|string $model): void
    {
        $class = $model instanceof Model ? $model::class : $model;
        $classes = $class === MediaFileVariant::class ? [$class, MediaFile::class] : [$class];
        $tags = array_map(CacheTag::publicModel(...), $classes);
        // Existing read caches also embed these related models.
        $tags = array_merge($tags, match ($class) {
            MediaFile::class, MediaFileVariant::class => [
                CacheTag::MEDIA, CacheTag::POSTS, CacheTag::PRODUCTS, CacheTag::PROJECTS,
                CacheTag::PAGES, CacheTag::STORES, CacheTag::HOME, CacheTag::SIDEBAR,
                CacheTag::NAVIGATION, CacheTag::SEO, CacheTag::SCHEMA, CacheTag::COMMENT_AUTHORS,
            ],
            TaxonomyTerm::class => [
                CacheTag::CATEGORIES, CacheTag::TAGS, CacheTag::POSTS, CacheTag::PRODUCTS,
                CacheTag::PROJECTS, CacheTag::HOME, CacheTag::SIDEBAR, CacheTag::SEO, CacheTag::SCHEMA,
            ],
            ProductSaleItem::class, ProductSaleItemDiscount::class, ProductSaleItemFile::class => [
                CacheTag::PRODUCTS, CacheTag::PRODUCT_BUY_BOX, CacheTag::HOME_PRODUCTS,
            ],
            User::class => [CacheTag::POST_DETAIL, CacheTag::PAGE_DETAIL, CacheTag::SIDEBAR_COMMENTS],
            default => [],
        });

        Cache::tags($tags)->flush();

        // Clear again after commit so concurrent reads cannot retain pre-commit data.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => Cache::tags($tags)->flush());
        }
    }
}
