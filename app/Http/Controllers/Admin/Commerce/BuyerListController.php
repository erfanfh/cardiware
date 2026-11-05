<?php

namespace App\Http\Controllers\Admin\Commerce;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * لیست خریداران بدون مبالغ: چه کسی، چه چیزی را، با کدام سفارش و چه زمانی
 * خریده است. برای نقش‌هایی مثل پشتیبانی که نباید درآمد و مبالغ را ببینند؛
 * هیچ ستون مالی در کوئری انتخاب یا به ویو داده نمی‌شود.
 */
class BuyerListController
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('order.buyers');

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'product' => ['nullable', 'string', 'max:150'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $search = trim((string)($filters['q'] ?? ''));
        $product = trim((string)($filters['product'] ?? ''));

        $items = OrderItem::query()
            ->select([
                'order_items.id', 'order_items.order_id', 'order_items.product_id',
                'order_items.purchasable_type', 'order_items.purchasable_id',
                'order_items.quantity', 'order_items.snapshot',
            ])
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.parent_order_item_id')
            ->where('orders.status', OrderStatusEnum::PAID->value)
            ->with([
                'order:id,order_number,user_id,paid_at',
                'order.user' => fn($query) => $query->withTrashed()->select(['id', 'first_name', 'last_name', 'display_name', 'mobile', 'email']),
                'product' => fn($query) => $query->withoutGlobalScopes()->select(['id', 'title']),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(function (Builder $query) use ($like, $search): void {
                    $query->where('orders.order_number', 'like', $like)
                        ->orWhereHas('order.user', fn(Builder $user) => $user->withTrashed()->where(function (Builder $user) use ($like): void {
                            $user->where('mobile', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('display_name', 'like', $like)
                                ->orWhere('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like);
                        }));
                });
            })
            ->when($product !== '', function (Builder $query) use ($product): void {
                $like = '%'.addcslashes($product, '%_\\').'%';
                $query->where(function (Builder $query) use ($like): void {
                    $query->whereHas('product', fn(Builder $p) => $p->withoutGlobalScopes()->where('title', 'like', $like))
                        ->orWhere('order_items.snapshot->title', 'like', $like);
                });
            })
            ->when($filters['from'] ?? null, fn(Builder $query, $from) => $query->where('orders.paid_at', '>=', $from))
            ->when($filters['to'] ?? null, fn(Builder $query, $to) => $query->where('orders.paid_at', '<=', \Illuminate\Support\Carbon::parse($to)->endOfDay()))
            ->orderByDesc('orders.paid_at')
            ->orderByDesc('order_items.id')
            ->paginate(30)
            ->withQueryString();

        $items->getCollection()->each(function (OrderItem $item): void {
            $snapshot = is_array($item->snapshot) ? $item->snapshot : [];
            $item->setAttribute('buyer_item_title', (string)(
                data_get($snapshot, 'title')
                ?: data_get($snapshot, 'product_sale_item_title')
                ?: data_get($snapshot, 'product_title')
                ?: $item->product?->title
                ?: 'آیتم #'.$item->purchasable_id
            ));
            // snapshot شامل قیمت است؛ به ویو نمی‌رود.
            $item->offsetUnset('snapshot');
        });

        return view('admin.commerce.buyers.index', [
            'items' => $items,
            'filters' => $filters,
        ]);
    }
}
