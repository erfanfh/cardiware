<?php

namespace App\Application\Shared\Cache;

final class CacheTag
{
    public const HOME = 'home';
    public const HOME_FREE_POSTS = 'home:free-posts';
    public const HOME_VIDEOS = 'home:videos';
    public const HOME_PROJECTS = 'home:projects';
    public const HOME_PRODUCTS = 'home:products';

    public const POSTS = 'posts';
    public const POST_LISTS = 'post-lists';

    public const PROJECTS = 'projects';

    public const PRODUCTS = 'products';
    public const STORES = 'stores';

    public const FOOTER_STATS = 'footer-stats';

    public const SEO = 'seo';
    public const SCHEMA = 'schema';
    public const MEDIA = 'media';

    public const CATEGORIES = 'categories';
    public const TAGS = 'tags';

    public const SITEMAP = 'sitemap';

    public const USER_ACCESS = 'user-access';
    public const ROLES = 'roles';

    public const PAGE_LAYOUTS = 'page-layouts';
    public const PAGE_LAYOUT_BUILDER = 'page-layout-builder';
    public const PAGE_LAYOUT_RENDER = 'page-layout-render';

    public const NAVIGATION = 'navigation';

    public const PAGE_VIEWS = 'page-views';

    public const POST_DETAIL = 'post-detail';
    public const RELATED_POSTS = 'related-posts';

    public const COMMENTS = 'comments';
    public const COMMENT_AUTHORS = 'comment-authors';

    public const SIDEBAR = 'sidebar';
    public const SIDEBAR_COMMENTS = 'sidebar:comments';
    public const SIDEBAR_CATEGORIES = 'sidebar:categories';
    public const SIDEBAR_PROJECTS = 'sidebar:projects';
    public const SIDEBAR_POPULARS = 'sidebar:populars';

    public const PROJECT_DETAIL = 'project-detail';
    public const PROJECT_LISTS = 'project-lists';
    public const PROJECT_STATS = 'project-stats';

    public const PRODUCT_DETAIL = 'product-detail';
    public const PRODUCT_LISTS = 'product-lists';
    public const PRODUCT_STATS = 'product-stats';
    public const PRODUCT_RELATED = 'product-related';
    public const PRODUCT_BUY_BOX = 'product-buy-box';

    public const STORE_DETAIL = 'store-detail';

    public const PAGES = 'pages';
    public const PAGE_DETAIL = 'page-detail';

    public const DASHBOARD = 'dashboard';
    public const DASHBOARD_TOP_POSTS = 'dashboard:top-posts';

    public const ACCOUNT = 'account';

    public const STORE = 'store';

    public const STORE_PRODUCTS = 'store-products';
    public const STORE_STATS = 'store-stats';

    public const PRODUCT_FILTERS = 'product-filters';

    // Launcher API layer. Everything a launcher client reads (catalog pages, manifests,
    // update/mod lists, portal content, per-user library) is served from here and
    // flushed by LauncherCacheInvalidator; see that class for which event flushes what.
    public const LAUNCHER = 'launcher';
    public const LAUNCHER_CATALOG = 'launcher:catalog';
    public const LAUNCHER_MANIFEST = 'launcher:manifest';
    public const LAUNCHER_CONTENT = 'launcher:content';
    public const LAUNCHER_PORTAL = 'launcher:portal';
    public const LAUNCHER_LIBRARY = 'launcher:library';
    public const LAUNCHER_DEVICES = 'launcher:devices';
    public const PRODUCT_FEATURED = 'product-featured';

    public static function post(int|string $postId): string
    {
        return "post:{$postId}";
    }

    public static function project(int|string $projectId): string
    {
        return "project:{$projectId}";
    }

    public static function store(int|string $storeId): string
    {
        return "store:{$storeId}";
    }

    public static function product(int|string $productId): string
    {
        return "product:{$productId}";
    }

    public static function page(int|string $pageId): string
    {
        return "page:{$pageId}";
    }

    public static function category(int|string $categoryId): string
    {
        return "category:{$categoryId}";
    }

    public static function tag(int|string $tagId): string
    {
        return "tag:{$tagId}";
    }

    public static function user(int|string $userId): string
    {
        return "user:{$userId}";
    }

    public static function userAccess(int|string $userId): string
    {
        return "user-access:{$userId}";
    }

    public static function pageLayout(string $key): string
    {
        return "page-layout:{$key}";
    }

    public static function navigationMenu(string $slug): string
    {
        return "navigation:menu:{$slug}";
    }

    public static function pageViews(string $modelClass, int|string $id): string
    {
        return "page-views:{$modelClass}:{$id}";
    }

    public static function commentable(string $type, int|string $id): string
    {
        return "commentable:{$type}:{$id}";
    }

    public static function launcherSaleItem(int|string $saleItemId): string
    {
        return "launcher:sale-item:{$saleItemId}";
    }

    public static function launcherUser(int|string $userId): string
    {
        return "launcher:user:{$userId}";
    }

    public static function launcherDevice(int|string $deviceId): string
    {
        return "launcher:device:{$deviceId}";
    }
}
