<?php

namespace App\Application\Seo\Support;

final class SchemaIdGenerator
{
    public function website(string $siteUrl): string
    {
        return rtrim($siteUrl, '/') . '/#website';
    }

    public function organization(string $siteUrl): string
    {
        return rtrim($siteUrl, '/') . '/#organization';
    }

    public function breadcrumb(string $url): string
    {
        return rtrim($url, '/') . '/#breadcrumb';
    }

    public function logoImage(string $siteUrl): string
    {
        return rtrim($siteUrl, '/') . '/#/schema/logo/image/';
    }

    public function primaryImage(string $pageUrl): string
    {
        return rtrim($pageUrl, '/') . '/#primaryimage';
    }

    public function article(string $pageUrl): string
    {
        return rtrim($pageUrl, '/') . '/#article';
    }

    public function person(string $siteUrl, string $identifier): string
    {
        return rtrim($siteUrl, '/') . '/#/schema/person/' . md5($identifier);
    }

    public function custom(string $pageUrl, string $suffix): string
    {
        return rtrim($pageUrl, '/') . $suffix;
    }
}
