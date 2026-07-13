<?php

namespace App\Http\Controllers\Api\V1\Catalog;

use App\Application\Catalog\Actions\GetCatalogProductDetailAction;
use App\Application\Catalog\Actions\GetCatalogProductsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Catalog\ProductIndexRequest;
use App\Http\Resources\Api\V1\Catalog\ProductDetailResource;
use App\Http\Resources\Api\V1\Catalog\ProductListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /**
     * Get catalog products
     *
     * Returns active store products with editions, versions, pricing,
     * categories, and optional authenticated-user ownership information.
     *
     * Authentication is optional. When a valid Bearer token is supplied,
     * the response indicates whether the user owns the product and each
     * individual sale item.
     *
     * @group Catalog
     * @unauthenticated
     *
     * @responseFile status=200 scenario="Catalog returned successfully" storage/responses/api/v1/catalog/index.success.json
     */
    public function index(
        ProductIndexRequest      $request,
        GetCatalogProductsAction $action,
    ): AnonymousResourceCollection
    {
        $products = $action->execute(
            user: $request->user(),
            filters: $request->validated(),
            perPage: $request->perPage(),
        );

        return ProductListResource::collection($products);
    }

    /**
     * Get product details
     *
     * Returns detailed product information including editions, versions,
     * final prices, galleries, requirements, installation steps, FAQs,
     * releases, attributes, add-ons, and optional ownership information.
     *
     * Authentication is optional.
     *
     * @group Catalog
     * @unauthenticated
     *
     * @urlParam productId integer required The product ID. Example: 20
     *
     * @responseFile status=200 scenario="Product returned successfully" storage/responses/api/v1/catalog/show.success.json
     * @responseFile status=404 scenario="Product not found" storage/responses/api/v1/errors/not-found.json
     */
    public function show(
        Request                       $request,
        int                           $productId,
        GetCatalogProductDetailAction $action,
    ): ProductDetailResource
    {
        $product = $action->execute(
            productId: $productId,
            user: $request->user(),
        );

        return ProductDetailResource::make($product);
    }
}
