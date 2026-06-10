<?php

namespace App\Application\Seo\Actions\Schema\Graphs;

use App\Application\Seo\Actions\Schema\AssembleSchemaGraphAction;
use App\Application\Seo\Actions\Schema\Breadcrumbs\BuildProductBreadcrumbSchemaAction;
use App\Application\Seo\Actions\Schema\BuildBaseSchemaNodesAction;
use App\Application\Seo\Actions\Schema\BuildPrimaryImageSchemaAction;
use App\Application\Seo\Actions\Schema\BuildProductSchemaAction;
use App\Application\Seo\Actions\Schema\BuildStoreSchemaAction;
use App\Application\Seo\Actions\Schema\Contexts\BuildProductSchemaContextAction;
use App\Application\Seo\Actions\Schema\Pages\BuildProductPageSchemaAction;
use App\Application\Seo\DTOs\ResolvedSeoPayloadDTO;
use App\Domain\Catalog\Models\Product;

final class BuildProductSchemaGraphAction
{
    public function __construct(
        protected BuildProductSchemaContextAction    $buildProductSchemaContextAction,
        protected BuildBaseSchemaNodesAction         $buildBaseSchemaNodesAction,
        protected BuildPrimaryImageSchemaAction      $buildPrimaryImageSchemaAction,
        protected BuildProductBreadcrumbSchemaAction $buildProductBreadcrumbSchemaAction,
        protected BuildProductSchemaAction           $buildProductSchemaAction,
        protected BuildProductPageSchemaAction       $buildProductPageSchemaAction,
        protected BuildStoreSchemaAction             $buildStoreSchemaAction,
        protected AssembleSchemaGraphAction          $assembleSchemaGraphAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(Product $product, ResolvedSeoPayloadDTO $payload): array
    {
        $product->loadMissing([
            'store',
            'picture.primaryVariant',
            'categories',
            'tags',
            'saleItems',
        ]);

        $context = $this->buildProductSchemaContextAction->execute($product, $payload);

        $baseNodes = $this->buildBaseSchemaNodesAction->execute($context->base);

        $primaryImageNode = $this->buildPrimaryImageSchemaAction->execute($context->base);

        $breadcrumbNode = $this->buildProductBreadcrumbSchemaAction->execute($context);

        $storeNode = $product->store ? $this->buildStoreSchemaAction->execute(
            store: $product->store,
            siteUrl: $context->base->siteUrl,
            storeUrl: $context->sellerUrl,
            description: $context->sellerDescription,
        ) : [];

        $productNode = $this->buildProductSchemaAction->execute(
            context: $context,
            primaryImageNode: $primaryImageNode,
        );

        $pageNode = $this->buildProductPageSchemaAction->execute(
            context: $context,
            websiteNode: $baseNodes->websiteNode,
            breadcrumbNode: $breadcrumbNode,
            productNode: $productNode,
        );

        return $this->assembleSchemaGraphAction->execute(
            pageNode: $pageNode,
            breadcrumbNode: $breadcrumbNode,
            websiteNode: $baseNodes->websiteNode,
            organizationNode: $baseNodes->organizationNode,
            extraNodes: [
                $primaryImageNode,
                $storeNode,
                $productNode,
            ],
        );
    }
}
