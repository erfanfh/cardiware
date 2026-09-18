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

class ProductTagListController extends Controller
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
            scope: SlugScopeEnum::TAXONOMY__PRODUCT_TAG,
            value: $path,
        ));

        if (! $resolvedSlug->found || ! $resolvedSlug->model instanceof TaxonomyTerm) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var TaxonomyTerm $tag */
        $tag = $resolvedSlug->model;
        $baseUrl = front_route('products.tags', ['path' => $tag->path]);

        if ($page === 1 && $request->routeIs('products.tags.page')) {
            return redirect()->to($baseUrl, 301);
        }

        $data = $productListPageDataAction->execute(
            request: $request,
            page: $page,
            perPage: (int) (get_setting('products', 'products_per_page') ?: 12),
            fixedTag: $tag,
        );

        $products = $data['products'];

        if ($page > 1 && $products->lastPage() > 0 && $page > $products->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $products);
        $request->attributes->set('seo_pagination_base_url', $baseUrl);

        return view('front.catalog.products.list', [
            ...$data,
            'tag' => $tag,
            'productListUrl' => $baseUrl,
            'productListRoute' => 'products.tags',
            'productListPageRoute' => 'products.tags.page',
            'productListRouteParameters' => ['path' => $tag->path],
        ]);
    }
}
