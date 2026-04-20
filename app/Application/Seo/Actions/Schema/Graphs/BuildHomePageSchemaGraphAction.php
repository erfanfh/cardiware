<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildHomePageSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildHomePageSchemaAction;

final class BuildHomePageSchemaGraphAction
{
    public function __construct(
        protected BuildHomePageSchemaContextAction $buildHomePageSchemaContextAction,
        protected BuildBaseSchemaNodesAction       $buildBaseSchemaNodesAction,
        protected BuildBreadcrumbSchemaAction      $buildBreadcrumbSchemaAction,
        protected BuildHomePageSchemaAction        $buildHomePageSchemaAction,
        protected AssembleSchemaGraphAction        $buildSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $context = $this->buildHomePageSchemaContextAction->execute();

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $breadcrumbNode = $this->buildBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildHomePageSchemaAction->execute(
            $context,
            $baseNodes->websiteNode,
            $baseNodes->organizationNode,
            $breadcrumbNode,
        );

        return $this->buildSchemaGraphAction->execute(
            pageNode: $pageNode,
            breadcrumbNode: $breadcrumbNode,
            websiteNode: $baseNodes->websiteNode,
            organizationNode: $baseNodes->organizationNode,
        );
    }
}
