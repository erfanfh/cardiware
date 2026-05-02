<?php

namespace App\Application\Shared\Cache;

final class CacheKey
{
    public static function settingsGroup(string $group): string
    {
        return "settings:group:{$group}";
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
        return 'home:free-posts:tab:' . md5(json_encode($payload));
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
        string     $group,
        string     $action
    ): string
    {
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

    public static function frontendNavigation(string $slug): string
    {
        return "navigation:frontend:{$slug}";
    }

    public static function contentSlug(string $slug): string
    {
        return 'content:slug:' . trim($slug);
    }

    public static function storedPageViews(string $modelClass, int|string $id): string
    {
        return "views:stored:{$modelClass}:{$id}";
    }

    public static function postSeoData(
        int|string $postId,
        int        $postUpdatedAt,
    ): string
    {
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

    public static function postList(int $page, int $perPage): string
    {
        return "content:posts:list:page:{$page}:per-page:{$perPage}";
    }

    public static function projectSeoData(
        int|string $projectId,
        int        $projectUpdatedAt
    ): string
    {
        return "seo-data:project:{$projectId}:{$projectUpdatedAt}";
    }

    public static function projectDetail(
        int|string $projectId,
        int        $updatedAt
    ): string
    {
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

    public static function pageSeoData(int|string $pageId, int $pageUpdatedAt): string
    {
        return "seo-data:page:{$pageId}:{$pageUpdatedAt}";
    }

    public static function pageDetailData(int|string $pageId, int $pageUpdatedAt): string
    {
        return "content:page:{$pageId}:detail-data:{$pageUpdatedAt}";
    }

    public static function taxonomySlug(string $taxonomy, string $routeKey): string
    {
        return "taxonomy:slug:{$taxonomy}:{$routeKey}";
    }

    public static function seoRedirect(string $path): string
    {
        return 'seo:redirect:' . md5($path);
    }

    public static function seoRedirectsVersion(): string
    {
        return 'seo:redirects:version';
    }
}
