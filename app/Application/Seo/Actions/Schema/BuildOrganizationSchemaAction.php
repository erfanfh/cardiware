<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildOrganizationSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $logoNode
     * @return array<string, mixed>
     */
    public function handle(SchemaContextDTO $context, array $logoNode = [], array $numberOfEmployeesNode = []): array
    {
        $organizationId = $this->schemaIdGenerator->organization($context->siteUrl);

        if (blank($context->organizationName) && blank($context->siteName)) {
            return [];
        }

        return [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $context->organizationName ?: $context->siteName,
            'alternateName' => $context->organizationAlternateName,
            'url' => $context->siteUrl,
            'logo' => $logoNode ?: null,
            'image' => $logoNode ? [
                '@id' => $logoNode['@id'],
            ] : null,
            'sameAs' => $context->organizationSameAs,
            'email' => $context->organizationEmail,
            'telephone' => $context->organizationTelephone,
            'legalName' => $context->organizationLegalName,
            'foundingDate' => $context->organizationFoundingDate,
            'numberOfEmployees' => $numberOfEmployeesNode ?: null,
        ];
    }
}
