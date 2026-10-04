<?php

namespace App\Http\Middleware;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Scopes\ActiveProductScope;
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

        if ($request->isMethod('GET')) {
            View::share('sellerUnansweredTicketCount', Ticket::query()
                ->where('status', TicketStatusEnum::OPEN)
                ->whereIn('product_id', Product::withoutGlobalScope(ActiveProductScope::class)
                    ->where('store_id', $store->getKey())
                    ->select('id'))
                ->count());
        }

        return $next($request);
    }
}
