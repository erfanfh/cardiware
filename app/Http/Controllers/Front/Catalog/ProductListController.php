<?php

namespace App\Http\Controllers\Front\Catalog;

use App\Application\Catalog\Actions\GetProductListPageDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductListController extends Controller
{
    public function __invoke(
        Request $request,
        GetProductListPageDataAction $action,
        ?int $page = null,
    ): View|RedirectResponse {
        $page = max(1, (int) ($page ?? $request->route('page', 1)));

        if ($page === 1 && $request->routeIs('products.list.page')) {
            return redirect()->to(front_route('products.list'), 301);
        }

        $data = $action->execute(
            request: $request,
            page: $page,
            perPage: (int) (get_setting('products', 'products_per_page') ?: 12),
        );

        $products = $data['products'];

        if ($page > 1 && $products->lastPage() > 0 && $page > $products->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $products);
        $request->attributes->set('seo_pagination_base_url', front_route('products.list'));

        return view('front.catalog.products.list', [
            ...$data,
            'productListUrl' => front_route('products.list'),
            'productListRoute' => 'products.list',
            'productListPageRoute' => 'products.list.page',
            'productListRouteParameters' => [],
        ]);
    }
}
