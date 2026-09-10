<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildProductCategoryBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostCategorySchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostCategorySchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildProductCategorySchemaGraphAction
{
    public function __construct(
        private readonly BuildPostCategorySchemaContextAction $buildCategorySchemaContextAction,
        private readonly BuildBaseSchemaNodesAction $buildBaseSchemaNodesAction,
        private readonly BuildPrimaryImageSchemaAction $buildPrimaryImageSchemaAction,
        private readonly BuildProductCategoryBreadcrumbSchemaAction $buildBreadcrumbSchemaAction,
        private readonly BuildPostCategorySchemaAction $buildCategorySchemaAction,
        private readonly AssembleSchemaGraphAction $assembleSchemaGraphAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(TaxonomyTerm $category, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildCategorySchemaContextAction->execute($category, $payload);
        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);
        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildBreadcrumbSchemaAction->execute($context, $category);
        $pageNode = $this->buildCategorySchemaAction->execute(
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
            extraNodes: array_values(array_filter([$primaryImageNode])),
        );
    }
}
