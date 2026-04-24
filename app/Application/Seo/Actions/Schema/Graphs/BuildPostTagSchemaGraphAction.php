<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildPostTagBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPostTagSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPostTagSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildPostTagSchemaGraphAction
{
    public function __construct(
        protected BuildPostTagSchemaContextAction    $buildPostTagSchemaContextAction,
        protected BuildBaseSchemaNodesAction         $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction      $buildPrimaryImageSchemaAction,
        protected BuildPostTagBreadcrumbSchemaAction $buildPostTagBreadcrumbSchemaAction,
        protected BuildPostTagSchemaAction           $buildPostTagSchemaAction,
        protected AssembleSchemaGraphAction          $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(TaxonomyTerm $tag, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildPostTagSchemaContextAction->execute($tag, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);
        $breadcrumbNode = $this->buildPostTagBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildPostTagSchemaAction->execute(
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
