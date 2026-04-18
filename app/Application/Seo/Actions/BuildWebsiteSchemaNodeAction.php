<?php

namespace App\Application\Seo\Actions;

class BuildWebsiteSchemaNodeAction
{
    public function __construct(
        private readonly GetSeoSchemaDefaultsAction $getSeoSchemaDefaultsAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(?string $description = null): array
    {
        $defaults = $this->getSeoSchemaDefaultsAction->execute();

        return array_filter([
            '@type' => 'WebSite',
            '@id' => $defaults['website_id'],
            'url' => $defaults['home_url'],
            'name' => $defaults['site_name'],
            'description' => $description,
            'publisher' => [
                '@id' => $defaults['organization_id'],
            ],
            'inLanguage' => 'fa-IR',
        ], fn($value) => !is_null($value) && $value !== '');
    }
}
