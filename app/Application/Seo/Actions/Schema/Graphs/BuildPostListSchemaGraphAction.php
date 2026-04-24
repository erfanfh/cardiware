<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildPostListBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostListSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostListSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;

final class BuildPostListSchemaGraphAction
{
    public function __construct(
        protected BuildPostListSchemaContextAction    $buildPostListSchemaContextAction,
        protected BuildBaseSchemaNodesAction          $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction       $buildPrimaryImageSchemaAction,
        protected BuildPostListBreadcrumbSchemaAction $buildPostListBreadcrumbSchemaAction,
        protected BuildPostListSchemaAction           $buildPostListSchemaAction,
        protected AssembleSchemaGraphAction           $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildPostListSchemaContextAction->execute($payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildPostListBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildPostListSchemaAction->execute(
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
