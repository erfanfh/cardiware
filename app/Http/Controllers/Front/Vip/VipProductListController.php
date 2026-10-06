<?php

namespace App\Http\Controllers\Front\Vip;

use App\Application\Vip\Actions\GetVipProductListAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VipProductListController extends Controller
{
    public function __invoke(Request $request, GetVipProductListAction $action, ?int $page = null): View|RedirectResponse
    {
        $page = max(1, (int) ($page ?? $request->route('page', 1)));

        if ($page === 1 && $request->routeIs('vip.products.page')) {
            return redirect()->to(front_route('vip.products.index'), 301);
        }

        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'latest';
        $products = $action->execute(
            page: $page,
            perPage: max(1, (int) (get_setting('products', 'products_per_page') ?: 12)),
            sort: $sort,
        )->withQueryString();

        if ($page > 1 && $page > $products->lastPage()) {
            abort(404);
        }

        $request->attributes->set('seo_paginator', $products);
        $request->attributes->set('seo_pagination_base_url', front_route('vip.products.index'));

        return view('front.vip.products', compact('products', 'sort'));
    }
}
