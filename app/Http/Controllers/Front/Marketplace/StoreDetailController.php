<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Marketplace\Actions\GetStoreDetailAction;
use App\Application\Marketplace\Actions\GetStoreDetailStatsAction;
use App\Application\Marketplace\Actions\GetStoreProductsAction;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreDetailController extends Controller
{
    public function __invoke(
        string $storeSlug,
        Request $request,
        ResolveSlugAction $resolveSlugAction,
        GetStoreDetailAction $getStoreDetailAction,
        GetStoreProductsAction $getStoreProductsAction,
        GetStoreDetailStatsAction $getStoreDetailStatsAction,
        StoreViewAction $storeViewAction,
    ): View|RedirectResponse {
        $resolvedSlug = $resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: SlugScopeEnum::STORE,
                value: $storeSlug,
            )
        );

        if (! $resolvedSlug->found) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var Store $store */
        $store = clone $getStoreDetailAction->execute($resolvedSlug->model);

        $storeProducts = $getStoreProductsAction->execute(
            store: $store,
            page: $request->integer('page', 1),
            perPage: 12
        );
        $store->setAttribute('products_count', $storeProducts->total());

        $stats = $getStoreDetailStatsAction->execute($store);

        $storeViewAction->execute($store);

        return view('front.marketplace.stores.detail', [
            'store' => $store,
            'storeProducts' => $storeProducts,
            'soldQuantity' => $stats['sold_quantity'],
        ]);
    }
}
