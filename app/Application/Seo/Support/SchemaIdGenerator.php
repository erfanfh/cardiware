<?php

namespace App\Application\Seo\Support;

final class SchemaIdGenerator
{
    public function website(string $siteUrl): string
    {
        return rtrim($siteUrl, '/').'/#website';
    }

    public function organization(string $siteUrl): string
    {
        return rtrim($siteUrl, '/').'/#organization';
    }

    public function breadcrumb(string $url): string
    {
        return rtrim($url, '/').'/#breadcrumb';
    }

    public function logoImage(string $siteUrl): string
    {
        return rtrim($siteUrl, '/').'/#/schema/logo/image/';
    }

    public function primaryImage(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#primaryimage';
    }

    public function article(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#article';
    }

    public function product(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#product';
    }

    public function video(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#video';
    }

    public function itemList(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#item-list';
    }

    public function offer(string $pageUrl, string|int|null $identifier = null): string
    {
        return rtrim($pageUrl, '/').($identifier ? '#offer-'.$identifier : '#offer');
    }

    public function aggregateOffer(string $pageUrl): string
    {
        return rtrim($pageUrl, '/').'/#aggregate-offer';
    }

    public function store(string $siteUrl, string|int $identifier): string
    {
        return rtrim($siteUrl, '/').'/#/schema/store/'.md5((string) $identifier);
    }

    public function storePage(string $storeUrl): string
    {
        return rtrim($storeUrl, '/').'/#store';
    }

    public function person(string $siteUrl, string $identifier): string
    {
        return rtrim($siteUrl, '/').'/#/schema/person/'.md5($identifier);
    }

    public function custom(string $pageUrl, string $suffix): string
    {
        return rtrim($pageUrl, '/').$suffix;
    }
}
