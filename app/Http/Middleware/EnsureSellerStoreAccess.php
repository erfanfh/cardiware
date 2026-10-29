<?php

namespace App\Http\Middleware;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Marketplace\Enums\SellerPermissionEnum;
use App\Domain\Support\Enums\TicketStatusEnum;
use App\Domain\Support\Models\Ticket;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = $request->route('store');

        abort_unless(
            $request->user()?->stores()->whereKey($store?->getKey())->exists(),
            403,
        );

        abort_unless($store->sellerCan(SellerPermissionEnum::ACCESS_PANEL), 403);
        $permission = SellerPermissionEnum::forRoute($request->route()->getName());
        abort_if($permission && ! $store->sellerCan($permission), 403);

        if ($request->isMethod('GET')) {
            View::share('sellerUnansweredTicketCount', $store->sellerCan(SellerPermissionEnum::VIEW_TICKETS) ? Ticket::query()
                ->where('status', TicketStatusEnum::OPEN)
                ->whereIn('product_id', Product::withoutGlobalScope(ActiveProductScope::class)
                    ->where('store_id', $store->getKey())
                    ->select('id'))
                ->count() : 0);
        }

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }
}
