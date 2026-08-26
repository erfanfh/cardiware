<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildPollPageSchemaAction
{
    /**
     * @param  array<string, mixed>  $websiteNode
     * @param  array<string, mixed>  $breadcrumbNode
     * @return array<string, mixed>
     */
    public function execute(
        SchemaContextDTO $context,
        array $websiteNode = [],
        array $breadcrumbNode = [],
    ): array {
        return [
            '@type' => 'WebPage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,
            'isPartOf' => isset($websiteNode['@id']) ? ['@id' => $websiteNode['@id']] : null,
            'datePublished' => $context->publishedTime,
            'dateModified' => $context->modifiedTime,
            'breadcrumb' => isset($breadcrumbNode['@id']) ? ['@id' => $breadcrumbNode['@id']] : null,
            'inLanguage' => $context->inLanguage,
        ];
    }
}
