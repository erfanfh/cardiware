<?php

namespace App\Http\Controllers\Admin\Commerce;

use App\Application\Commerce\Listings\OrderListing;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Http\Requests\Admin\Commerce\IndexOrderRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrderController
{
    public function index(IndexOrderRequest $request, OrderListing $orderListing): View
    {
        Gate::authorize('order.list');

        $orders = $orderListing
            ->handle(Order::with(['user']), $request->validated())
            ->paginate($orderListing->perPage())
            ->withQueryString();

        $statuses = collect(OrderStatusEnum::cases())
            ->mapWithKeys(fn(OrderStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        $hasActiveFilters = $orderListing->hasActiveFilters($request->validated());

        return view('admin.commerce.orders.index', compact('orders', 'statuses', 'hasActiveFilters'));
    }
}
