<?php

namespace App\Application\Seo\Support;

final class SchemaIdGenerator
{
    public function website(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/#website';
    }

    public function organization(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/#organization';
    }

    public function breadcrumb(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/#breadcrumb';
    }

    public function logoImage(string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . '/#/schema/logo/image/';
    }
}
