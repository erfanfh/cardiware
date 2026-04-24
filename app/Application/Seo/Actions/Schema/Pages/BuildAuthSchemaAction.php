<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildAuthSchemaAction
{
    public function execute(
        SchemaContextDTO $context,
        array            $websiteNode = [],
        array            $organizationNode = [],
        array            $breadcrumbNode = [],
    ): array
    {
        $websiteId = $websiteNode['@id'] ?? null;
        $breadcrumbId = $breadcrumbNode['@id'] ?? null;

        return [
            '@type' => 'WebPage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,
            'isPartOf' => $websiteId ? [
                '@id' => $websiteId,
            ] : null,
            'breadcrumb' => $breadcrumbId ? [
                '@id' => $breadcrumbId,
            ] : null,
            'inLanguage' => $context->inLanguage,
        ];
    }
}
