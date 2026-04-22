<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildProjectBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProjectEntitySchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildProjectSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildProjectSchemaAction;
use App\Application\Seo\DTOs\ResolvedProjectSeoPayloadDTO;
use App\Domain\Project\Models\Project;

final class BuildProjectSchemaGraphAction
{
    public function __construct(
        protected BuildProjectSchemaContextAction    $buildProjectSchemaContextAction,
        protected BuildBaseSchemaNodesAction         $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction      $buildPrimaryImageSchemaAction,
        protected BuildProjectEntitySchemaAction     $buildProjectEntitySchemaAction,
        protected BuildProjectBreadcrumbSchemaAction $buildProjectBreadcrumbSchemaAction,
        protected BuildProjectSchemaAction           $buildProjectSchemaAction,
        protected AssembleSchemaGraphAction          $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Project $project, ResolvedProjectSeoPayloadDTO $payload): array
    {
        $context = $this->buildProjectSchemaContextAction->execute($project, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildProjectBreadcrumbSchemaAction->execute($context);

        $projectNode = $this->buildProjectEntitySchemaAction->execute(
            $project,
            $context,
            $baseNodes->organizationNode,
            $primaryImageNode,
        );

        $pageNode = $this->buildProjectSchemaAction->execute(
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
                $projectNode,
                $primaryImageNode,
            ])),
        );
    }
}
