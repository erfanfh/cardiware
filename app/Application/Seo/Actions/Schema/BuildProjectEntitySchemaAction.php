<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Project\Models\Project;

final class BuildProjectEntitySchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $primaryImageNode
     * @return array<string, mixed>
     */
    public function execute(
        Project          $project,
        SchemaContextDTO $context,
        array            $organizationNode = [],
        array            $primaryImageNode = [],
    ): array
    {
        $organizationId = $organizationNode['@id'] ?? null;
        $primaryImageId = $primaryImageNode['@id'] ?? null;

        $node = [
            '@type' => 'Project',
            '@id' => $this->schemaIdGenerator->custom($context->pageUrl, '#project'),
            'name' => $context->headline ?: $context->pageTitle,
            'description' => $context->pageDescription,
            'url' => $context->pageUrl,
            'keywords' => $context->keywords,
            'image' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : $context->pageImageUrl,
            'parentOrganization' => $organizationId ? [
                '@id' => $organizationId,
            ] : null,
        ];

        if (!blank($project->status_label ?? $project->status?->value ?? $project->status ?? null)) {
            $node['additionalProperty'] = [
                [
                    '@type' => 'PropertyValue',
                    'name' => 'status',
                    'value' => $project->status_label ?? $project->status?->value ?? $project->status,
                ],
            ];
        }

        return $node;
    }
}
