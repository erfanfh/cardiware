<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildStoreBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\BuildStoreEntitySchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildStoreSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildStoreSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Marketplace\Models\Store;

final class BuildStoreSchemaGraphAction
{
    public function __construct(
        protected BuildStoreSchemaContextAction    $buildStoreSchemaContextAction,
        protected BuildBaseSchemaNodesAction       $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction    $buildPrimaryImageSchemaAction,
        protected BuildStoreEntitySchemaAction     $buildStoreEntitySchemaAction,
        protected BuildStoreBreadcrumbSchemaAction $buildStoreBreadcrumbSchemaAction,
        protected BuildStoreSchemaAction           $buildStoreSchemaAction,
        protected AssembleSchemaGraphAction        $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Store $store, ResolvedSeoPayloadDTO $payload): array
    {
        $context = $this->buildStoreSchemaContextAction->execute($store, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context);

        $breadcrumbNode = $this->buildStoreBreadcrumbSchemaAction->execute($context);

        $storeNode = $this->buildStoreEntitySchemaAction->execute(
            $store,
            $context,
            $baseNodes->organizationNode,
            $primaryImageNode,
        );

        $pageNode = $this->buildStoreSchemaAction->execute(
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
                $storeNode,
                $primaryImageNode,
            ])),
        );
    }
}
