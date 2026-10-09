<?php

namespace App\Application\Shared\Cache;

final class CacheKey
{
    public static function settingsGroup(string $group): string
    {
        return "settings:group:{$group}";
    }

    public static function launcherBootstrapReleases(string $platform, string $channel): string
    {
        return "launcher:bootstrap:releases:{$platform}:{$channel}";
    }

    public static function organizationLogo(int|string $mediaId): string
    {
        return "seo:organization-logo:{$mediaId}";
    }

    public static function footerStats(): string
    {
        return 'footer:stats';
    }

    public static function homeFreePostsTab(array $payload): string
    {
        return 'home:free-posts:tab:'.md5(json_encode($payload));
    }

    public static function homeVideosBlock(): string
    {
        return 'home:videos:block';
    }

    public static function homeProjectsBlock(): string
    {
        return 'home:projects:block';
    }

    public static function defaultRole(): string
    {
        return 'identity:roles:default';
    }

    public static function userAccessAbility(
        int|string $userId,
        string $group,
        string $action
    ): string {
        return "identity:user-access:{$userId}:{$group}:{$action}";
    }

    public static function pageLayoutBuilder(string $key): string
    {
        return "layout:page:{$key}:builder";
    }

    public static function pageLayoutRender(string $key): string
    {
        return "layout:page:{$key}:render";
    }

    public static function frontendNavigation(string $slug, int|string $revision): string
    {
        return "navigation:frontend:v3:{$slug}:revision:{$revision}";
    }

    public static function contentSlug(string $slug): string
    {
        return 'content:slug:'.trim($slug);
    }

    public static function storedPageViews(string $modelClass, int|string $id): string
    {
        return "views:stored:{$modelClass}:{$id}";
    }

    public static function postSeoData(
        int|string $postId,
        int $postUpdatedAt,
    ): string {
        return "seo-data:post:{$postId}:{$postUpdatedAt}";
    }

    public static function postDetailData(int|string $postId, int $postUpdatedAt): string
    {
        return "content:post:{$postId}:detail-data:{$postUpdatedAt}";
    }

    public static function postRelated(int|string $postId, int $postUpdatedAt): string
    {
        return "content:post:{$postId}:related:{$postUpdatedAt}";
    }

    public static function sidebarLatestComments(): string
    {
        return 'sidebar:latest-comments';
    }

    public static function sidebarCategories(): string
    {
        return 'sidebar:categories';
    }

    public static function sidebarProjects(): string
    {
        return 'sidebar:projects';
    }

    public static function sidebarPopulars(string $date): string
    {
        return "sidebar:populars:{$date}";
    }

    public static function postList(int $page, int $perPage): string
    {
        return "content:posts:list:page:{$page}:per-page:{$perPage}";
    }

    public static function projectSeoData(
        int|string $projectId,
        int $projectUpdatedAt
    ): string {
        return "seo-data:project:{$projectId}:{$projectUpdatedAt}";
    }

    public static function projectDetail(
        int|string $projectId,
        int $updatedAt
    ): string {
        return "project:detail:{$projectId}:{$updatedAt}";
    }

    public static function projectList(int $page, int $perPage): string
    {
        return "project:list:page:{$page}:per-page:{$perPage}";
    }

    public static function projectStats(): string
    {
        return 'project:stats';
    }

    public static function productSeoData(
        int|string $productId,
        int $productUpdatedAt
    ): string {
        return "seo-data:product:v2:{$productId}:{$productUpdatedAt}";
    }

    public static function productSlug(string $slug): string
    {
        return 'product:slug:'.trim($slug);
    }

    public static function pageSeoData(int|string $pageId, int $pageUpdatedAt): string
    {
        return "seo-data:page:{$pageId}:{$pageUpdatedAt}";
    }

    public static function pageDetailData(int|string $pageId, int $pageUpdatedAt): string
    {
        return "content:page:{$pageId}:detail-data:{$pageUpdatedAt}";
    }

    public static function storeSeoData(
        int|string $storeId,
        int $storeUpdatedAt,
    ): string {
        return "seo-data:post:{$storeId}:{$storeUpdatedAt}";
    }

    public static function taxonomySlug(string $taxonomy, string $routeKey): string
    {
        return "taxonomy:slug:{$taxonomy}:{$routeKey}";
    }

    public static function seoRedirect(string $version, string $path): string
    {
        return 'seo:redirect:'.$version.':'.md5($path);
    }

    public static function seoRedirectsVersion(): string
    {
        return 'seo:redirects:version';
    }

    public static function commentableApprovedCommentsCount(string $type, int|string $id): string
    {
        return "commentable:{$type}:{$id}:approved_comments_count";
    }

    public static function commentableAverageRating(string $type, int|string $id): string
    {
        return "commentable:{$type}:{$id}:average_rating";
    }

    public static function commentableRatingSummary(string $type, int|string $id): string
    {
        return "commentable:{$type}:{$id}:rating_summary";
    }

    public static function commentableReviews(string $type, int|string $id): string
    {
        return "commentable:{$type}:{$id}:reviews";
    }

    public static function dashboardYesterdayTopPosts(string $date, int $limit): string
    {
        return "dashboard:yesterday-top-posts:{$date}:limit:{$limit}";
    }

    public static function accountUserStats(int|string $userId): string
    {
        return "account:user:{$userId}:stats";
    }

    public static function userFavoriteProductIds(int|string $userId): string
    {
        return "account:user:{$userId}:favorite-product-ids";
    }

    public static function userAvatar(int|string $userId): string
    {
        return "user:{$userId}:avatar";
    }

    public static function productDetail(int|string $productId, int $updatedAt): string
    {
        return "product:detail:{$productId}:{$updatedAt}";
    }

    public static function productDetailStats(int|string $productId): string
    {
        return "product:detail:{$productId}:stats";
    }

    public static function productRelated(int|string $productId, int $updatedAt): string
    {
        return "product:detail:{$productId}:related:{$updatedAt}";
    }

    public static function storeDetail(int|string $storeId, int $storeUpdatedAt): string
    {
        return "store:detail:{$storeId}:{$storeUpdatedAt}";
    }

    public static function storeProducts(
        int|string $storeId,
        int $page,
        int $perPage
    ): string {
        return "store:{$storeId}:products:page:{$page}:per-page:{$perPage}";
    }

    public static function storeDetailStats(int|string $storeId): string
    {
        return "store:detail:{$storeId}:stats";
    }

    public static function productListPage(array $payload): string
    {
        return 'product:list:page:'.md5(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    public static function productAvailablePriceRange(
        int|string|null $categoryId = null,
        int|string|null $tagId = null,
    ): string {
        return 'product:list:available-price-range:category:'.($categoryId ?: 'all')
            .':tag:'.($tagId ?: 'all');
    }

    public static function productCategoryTree(int|string|null $parentId = null): string
    {
        return 'product:list:category-tree:'.($parentId ?: 'root');
    }

    public static function productFeatured(
        int|string|null $categoryId = null,
        int|string|null $tagId = null,
    ): string {
        return 'product:list:featured:category:'.($categoryId ?: 'all')
            .':tag:'.($tagId ?: 'all');
    }

    public static function productCategoryDescendants(array $categoryIds): string
    {
        sort($categoryIds);

        return 'product:category-descendants:'.md5(json_encode($categoryIds, JSON_THROW_ON_ERROR));
    }

    public static function launcherCatalogPage(string $search, int $page, int $perPage): string
    {
        return 'launcher:catalog:page:' . md5($search) . ":{$page}:{$perPage}";
    }

    public static function launcherCatalogItem(int|string $saleItemId, int $manifestRevision): string
    {
        return "launcher:catalog:item:{$saleItemId}:r{$manifestRevision}";
    }

    public static function launcherManifest(int|string $saleItemId, int $manifestRevision): string
    {
        return "launcher:manifest:{$saleItemId}:r{$manifestRevision}";
    }

    public static function launcherUpdates(int|string $saleItemId, int $manifestRevision): string
    {
        return "launcher:updates:{$saleItemId}:r{$manifestRevision}";
    }

    public static function launcherMods(int|string $saleItemId, int $manifestRevision): string
    {
        return "launcher:mods:{$saleItemId}:r{$manifestRevision}";
    }

    public static function launcherSlides(): string
    {
        return 'launcher:portal:slides';
    }

    public static function launcherNotifications(): string
    {
        return 'launcher:portal:notifications';
    }

    public static function launcherGuides(): string
    {
        return 'launcher:portal:guides';
    }

    public static function launcherLibrary(int|string $userId, int $page, int $perPage): string
    {
        return "launcher:library:{$userId}:{$page}:{$perPage}";
    }

    public static function launcherDevice(int|string $deviceId): string
    {
        return "launcher:device:{$deviceId}";
    }

    public static function productBuyBoxData(int|string $productId, int $productUpdatedAt): string
    {
        return "product:{$productId}:buy-box-data:{$productUpdatedAt}";
    }
}
