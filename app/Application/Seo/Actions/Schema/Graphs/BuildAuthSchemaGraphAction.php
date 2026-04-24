<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildAuthBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildAuthSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildAuthSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;

final class BuildAuthSchemaGraphAction
{
    public function __construct(
        protected BuildAuthSchemaContextAction    $buildAuthSchemaContextAction,
        protected BuildBaseSchemaNodesAction      $buildBaseSchemaNodesAction,
        protected BuildAuthBreadcrumbSchemaAction $buildAuthBreadcrumbSchemaAction,
        protected BuildAuthSchemaAction           $buildAuthSchemaAction,
        protected AssembleSchemaGraphAction       $assembleSchemaGraphAction,
    )
    {
    }

    public function execute(ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildAuthSchemaContextAction->execute($payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);
        $breadcrumbNode = $this->buildAuthBreadcrumbSchemaAction->execute($context);

        $pageNode = $this->buildAuthSchemaAction->execute(
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
