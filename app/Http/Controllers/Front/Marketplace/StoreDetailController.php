<?php

namespace App\Http\Controllers\Front\Marketplace;

use App\Application\Commerce\Reports\ProductSalesReport;
use App\Application\Slug\Actions\ResolveSlugAction;
use App\Application\Slug\DTOs\ResolveSlugDTO;
use App\Application\System\Actions\StoreViewAction;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Slug\Enums\SlugScopeEnum;
use App\Http\Controllers\Controller;

class StoreDetailController extends Controller
{
    public function __invoke(
        string             $storeSlug,
        ResolveSlugAction  $resolveSlugAction,
        ProductSalesReport $productSalesReport,
    )
    {
        $resolvedSlug = $resolveSlugAction->execute(
            new ResolveSlugDTO(
                scope: SlugScopeEnum::STORE,
                value: $storeSlug,
            )
        );

        if (!$resolvedSlug->found) {
            abort(404);
        }

        if ($resolvedSlug->shouldRedirect && filled($resolvedSlug->redirectTo)) {
            return redirect()->to($resolvedSlug->redirectTo, 301);
        }

        /** @var Store $store */
        $store = $resolvedSlug->model
            ->load([
                'picture.primaryVariant',
            ])
            ->loadCount([
                'products',
            ]);

        $storeProducts = $store
            ->products()
            ->with([
                'picture.primaryVariant',
                'store',
                'saleItems'
            ])
            ->paginate(12);

        $salesSummary = $productSalesReport->storeSummary($store->id);
        $soldQuantity = $salesSummary['sold_quantity'];

        app(StoreViewAction::class)->execute($store);

        return view('front.marketplace.stores.detail', compact('store', 'storeProducts', 'soldQuantity'));
    }
}
