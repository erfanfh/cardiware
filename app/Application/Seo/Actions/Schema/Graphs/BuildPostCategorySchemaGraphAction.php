<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildPostCategoryBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostCategorySchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostCategorySchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildPostCategorySchemaGraphAction
{
    public function __construct(
        protected BuildPostCategorySchemaContextAction    $buildPostCategorySchemaContextAction,
        protected BuildBaseSchemaNodesAction              $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction           $buildPrimaryImageSchemaAction,
        protected BuildPostCategoryBreadcrumbSchemaAction $buildPostCategoryBreadcrumbSchemaAction,
        protected BuildPostCategorySchemaAction           $buildPostCategorySchemaAction,
        protected AssembleSchemaGraphAction               $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(TaxonomyTerm $category, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildPostCategorySchemaContextAction->execute($category, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildPostCategoryBreadcrumbSchemaAction->execute($context, $category);

        $pageNode = $this->buildPostCategorySchemaAction->execute(
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
