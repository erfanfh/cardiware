<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildPageBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildPageSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildPageSchemaAction;
use App\Application\Seo\DTOs\ResolvedPageSeoPayloadDTO;
use App\Domain\Content\Models\Page;

final class BuildPageSchemaGraphAction
{
    public function __construct(
        protected BuildPageSchemaContextAction    $buildPageSchemaContextAction,
        protected BuildBaseSchemaNodesAction      $buildBaseSchemaNodesAction,
        protected BuildPageBreadcrumbSchemaAction $buildPageBreadcrumbSchemaAction,
        protected BuildPageSchemaAction           $buildPageSchemaAction,
        protected AssembleSchemaGraphAction       $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Page $page, ResolvedPageSeoPayloadDTO $payload): array
    {
        $context = $this->buildPageSchemaContextAction->execute($page, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $breadcrumbNode = $this->buildPageBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildPageSchemaAction->execute(
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
        );
    }
}
