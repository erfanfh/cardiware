<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildProductTagBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostCategorySchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostCategorySchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildProductTagSchemaGraphAction
{
    public function __construct(
        private readonly BuildPostCategorySchemaContextAction $buildTagSchemaContextAction,
        private readonly BuildBaseSchemaNodesAction $buildBaseSchemaNodesAction,
        private readonly BuildPrimaryImageSchemaAction $buildPrimaryImageSchemaAction,
        private readonly BuildProductTagBreadcrumbSchemaAction $buildBreadcrumbSchemaAction,
        private readonly BuildPostCategorySchemaAction $buildTagSchemaAction,
        private readonly AssembleSchemaGraphAction $assembleSchemaGraphAction,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(TaxonomyTerm $tag, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildTagSchemaContextAction->execute($tag, $payload);
        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);
        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildBreadcrumbSchemaAction->execute($context);
        $pageNode = $this->buildTagSchemaAction->execute(
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
