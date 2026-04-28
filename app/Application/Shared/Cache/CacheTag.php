<?php

namespace App\Application\Shared\Cache;

final class CacheTag
{
    public const SETTINGS = 'settings';

    public const HOME = 'home';
    public const HOME_FREE_POSTS = 'home:free-posts';
    public const HOME_VIDEOS = 'home:videos';
    public const HOME_PROJECTS = 'home:projects';

    public const POSTS = 'posts';
    public const POST_LISTS = 'post-lists';

    public const PROJECTS = 'projects';

    public const COMMENTS = 'comments';
    public const FOOTER_STATS = 'footer-stats';

    public const SEO = 'seo';
    public const SCHEMA = 'schema';
    public const MEDIA = 'media';

    public const NAVIGATION = 'navigation';

    public const CATEGORIES = 'categories';
    public const TAGS = 'tags';

    public const SITEMAP = 'sitemap';

    public const USER_ACCESS = 'user-access';
    public const ROLES = 'roles';

    public const PAGE_LAYOUTS = 'page-layouts';
    public const PAGE_LAYOUT_BUILDER = 'page-layout-builder';
    public const PAGE_LAYOUT_RENDER = 'page-layout-render';

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

    public static function pageLayout(string $key): string
    {
        return "page-layout:{$key}";
    }
}
