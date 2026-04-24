<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildSearchBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildSearchSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildSearchSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;

final class BuildSearchSchemaGraphAction
{
    public function __construct(
        protected BuildSearchSchemaContextAction    $buildSearchSchemaContextAction,
        protected BuildBaseSchemaNodesAction        $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction     $buildPrimaryImageSchemaAction,
        protected BuildSearchBreadcrumbSchemaAction $buildSearchBreadcrumbSchemaAction,
        protected BuildSearchSchemaAction           $buildSearchSchemaAction,
        protected AssembleSchemaGraphAction         $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildSearchSchemaContextAction->execute($payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);
        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildSearchBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildSearchSchemaAction->execute(
            $context,
            $baseNodes->websiteNode,
            $baseNodes->organizationNode,
            $breadcrumbNode,
        );

        return $this->assembleSchemaGraphAction->execute(
            pageNode: $pageNode,
            breadcrumbNode: $breadcrumbNode,
            websiteNode: $baseNodes->websiteNode,
            organizationNode: $baseNodes->organizationNode,
            extraNodes: array_values(array_filter([
                $primaryImageNode,
            ])),
        );
    }
}
