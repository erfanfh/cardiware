<?php

namespace App\Http\Controllers\Front\Taxonomy;

use App\Application\Catalog\Actions\GetProductListPageDataAction;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryListController extends Controller
{
    public function __invoke(
        Request $request,
        string $path,
        ResolveSlugAction $resolveSlugAction,
        GetProductListPageDataAction $productListPageDataAction,
        ?int $page = null,
    ): View|RedirectResponse {
        $page = max(1, (int) ($page ?? $request->route('page', 1)));

        $resolvedSlug = $resolveSlugAction->execute(new ResolveSlugDTO(
            scope: SlugScopeEnum::TAXONOMY__PRODUCT_CATEGORY,
            value: $path,
        ));

        if (! $resolvedSlug->found || ! $resolvedSlug->model instanceof TaxonomyTerm) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var TaxonomyTerm $category */
        $category = $resolvedSlug->model;
        $baseUrl = front_route('products.categories', ['path' => $category->path]);

        if ($page === 1 && $request->routeIs('products.categories.page')) {
            return redirect()->to($baseUrl, 301);
        }

        $data = $productListPageDataAction->execute(
            request: $request,
            page: $page,
            perPage: (int) (get_setting('products', 'products_per_page') ?: 12),
            fixedCategory: $category,
        );

        $products = $data['products'];

        if ($page > 1 && $products->lastPage() > 0 && $page > $products->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $products);
        $request->attributes->set('seo_pagination_base_url', $baseUrl);

        return view('front.catalog.products.list', [
            ...$data,
            'category' => $category,
            'productListUrl' => $baseUrl,
            'productListRoute' => 'products.categories',
            'productListPageRoute' => 'products.categories.page',
            'productListRouteParameters' => ['path' => $category->path],
        ]);
    }
}
