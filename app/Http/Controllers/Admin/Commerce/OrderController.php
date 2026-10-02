<?php

namespace App\Http\Controllers\Admin\Commerce;

use App\Application\Commerce\Actions\Order\AddOrderDescriptionAction;
use App\Application\Commerce\Actions\Order\UpdateOrderStatusAction;
use App\Application\Commerce\Listings\OrderListing;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use App\Domain\Commerce\Models\Order;
use App\Http\Requests\Admin\Commerce\IndexOrderRequest;
use App\Http\Requests\Admin\Commerce\UpdateOrderDescriptionRequest;
use App\Http\Requests\Admin\Commerce\UpdateOrderStatusRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

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

    public function edit(Order $order): View
    {
        Gate::authorize('order.edit');

        $order->load([
            'user' => fn($query) => $query->withTrashed(),
            'latestPayment',
            'refund.walletTransaction',
            'descriptions',
            'rootItems.children',
            'items',
            'rootItems.purchasable',
            'rootItems.children.purchasable',
            'rootItems.product',
            'rootItems.store',
            'rootItems.purchaseAccess',
        ]);

        $statuses = collect(OrderStatusEnum::cases())
            ->mapWithKeys(fn(OrderStatusEnum $status) => [
                $status->value => $status->label(),
            ])
            ->all();

        return view('admin.commerce.orders.edit', compact('order', 'statuses'));
    }

    public function update(
        UpdateOrderStatusRequest $request,
        Order                    $order,
        UpdateOrderStatusAction  $updateOrderStatusAction,
    ): RedirectResponse
    {
        Gate::authorize('order.edit');

        try {
            $updateOrderStatusAction->execute(
                order: $order,
                newStatus: OrderStatusEnum::from($request->validated('status')),
                refundMethod: $request->validated('refund_method')
                    ? OrderRefundMethodEnum::from($request->validated('refund_method'))
                    : null,
                admin: $request->user(),
            );

            return redirect()
                ->route('admin.orders.edit', $order)
                ->with('success', 'وضعیت سفارش با موفقیت به‌روزرسانی شد.');
        } catch (DomainException $exception) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', match ($exception->getMessage()) {
                    'Order status transition is not allowed.' => 'تغییر وضعیت انتخاب‌شده برای این سفارش مجاز نیست.',
                    'This order has already been refunded.' => 'این سفارش قبلاً مسترد شده است.',
                    'Refunded order cannot be marked as paid again.' => 'سفارش مستردشده را نمی‌توان دوباره پرداخت‌شده کرد.',
                    'The user wallet is unavailable.' => 'کیف پول کاربر فعال نیست. روش واریز دستی را انتخاب کنید.',
                    'Refund method is required.' => 'روش استرداد را انتخاب کنید.',
                    default => 'امکان تغییر وضعیت سفارش وجود ندارد.',
                });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'خطایی در به‌روزرسانی وضعیت سفارش رخ داد. لطفا دوباره تلاش کنید.');
        }
    }

    public function updateDescription(
        UpdateOrderDescriptionRequest $request,
        Order                         $order,
        AddOrderDescriptionAction     $addOrderDescriptionAction,
    ): RedirectResponse
    {
        Gate::authorize('order.edit');

        $addOrderDescriptionAction->execute(
            order: $order,
            description: $request->validated('description'),
            admin: $request->user(),
        );

        return redirect()
            ->route('admin.orders.edit', $order)
            ->with('success', 'توضیح جدید برای سفارش ثبت شد.');
    }
}
