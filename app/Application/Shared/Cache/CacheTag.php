<?php

namespace App\Application\Shared\Cache;

final class CacheTag
{
    public const HOME = 'home';
    public const HOME_FREE_POSTS = 'home:free-posts';
    public const HOME_VIDEOS = 'home:videos';
    public const HOME_PROJECTS = 'home:projects';

    public const POSTS = 'posts';
    public const POST_LISTS = 'post-lists';

    public const PROJECTS = 'projects';

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

    public static function post(int|string $postId): string
    {
        return "post:{$postId}";
    }

    public static function project(int|string $projectId): string
    {
        return "project:{$projectId}";
    }

    public static function category(int|string $categoryId): string
    {
        return "category:{$categoryId}";
    }

    public static function tag(int|string $tagId): string
    {
        return "tag:{$tagId}";
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
}
