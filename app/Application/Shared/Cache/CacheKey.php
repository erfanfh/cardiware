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
        int        $seoMetaUpdatedAt = 0
    ): string
    {
        return "seo-data:post:{$postId}:{$postUpdatedAt}:{$seoMetaUpdatedAt}";
    }
}
