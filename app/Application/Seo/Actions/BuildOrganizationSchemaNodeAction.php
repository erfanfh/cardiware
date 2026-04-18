<?php

namespace App\Application\Seo\Actions;

class BuildOrganizationSchemaNodeAction
{
    public function __construct(
        private readonly GetSeoSchemaDefaultsAction $getSeoSchemaDefaultsAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $defaults = $this->getSeoSchemaDefaultsAction->execute();

        return array_filter([
            '@type' => 'Organization',
            '@id' => $defaults['organization_id'],
            'name' => $defaults['organization_name'],
            'alternateName' => $defaults['organization_alternate_name'],
            'url' => $defaults['organization_url'],
            'email' => $defaults['organization_email'],
            'telephone' => $defaults['organization_phone'],
            'sameAs' => $defaults['organization_same_as'] !== [] ? $defaults['organization_same_as'] : null,
            'logo' => $defaults['organization_logo']
                ? [
                    '@type' => 'ImageObject',
                    'url' => $defaults['organization_logo'],
                ]
                : null,
        ], fn($value) => !is_null($value) && $value !== '');
    }
}
