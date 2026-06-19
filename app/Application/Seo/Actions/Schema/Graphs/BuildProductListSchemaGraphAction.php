<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildProjectListBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildProjectListSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildProjectListSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;

final class BuildProductListSchemaGraphAction
{
    public function __construct(
        protected BuildProjectListSchemaContextAction    $buildProjectListSchemaContextAction,
        protected BuildBaseSchemaNodesAction             $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction          $buildPrimaryImageSchemaAction,
        protected BuildProjectListBreadcrumbSchemaAction $buildProjectListBreadcrumbSchemaAction,
        protected BuildProjectListSchemaAction           $buildProjectListSchemaAction,
        protected AssembleSchemaGraphAction              $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildProjectListSchemaContextAction->execute($payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);
        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildProjectListBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildProjectListSchemaAction->execute(
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
