<?php

namespace App\Livewire\Front\Account;

use App\Application\Commerce\Actions\Order\GetUserOrdersAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class MyOrdersSection extends Component
{
    /**
     * Get current user's orders.
     *
     * @return Collection<int, Order>
     */
    #[Computed]
    public function orders(): Collection
    {
        if (!auth()->check()) {
            return collect();
        }

        return app(GetUserOrdersAction::class)->execute(
            userId: (int)auth()->id(),
        );
    }

    /**
     * Resolve order status label.
     */
    public function orderStatusLabel(Order $order): string
    {
        return match ($order->status) {
            OrderStatusEnum::PENDING_PAYMENT => 'در انتظار پرداخت',
            OrderStatusEnum::PAID => 'پرداخت شده',
            OrderStatusEnum::FAILED => 'ناموفق',
            OrderStatusEnum::CANCELLED => 'لغو شده',
            OrderStatusEnum::REFUNDED => 'مسترد شده',
            default => 'نامشخص',
        };
    }

    /**
     * Resolve order status badge classes.
     */
    public function orderStatusClass(Order $order): string
    {
        return match ($order->status) {
            OrderStatusEnum::PAID => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            OrderStatusEnum::PENDING_PAYMENT => 'text-amber-700 bg-amber-50 border-amber-200',
            OrderStatusEnum::FAILED, OrderStatusEnum::CANCELLED => 'text-red-700 bg-red-50 border-red-200',
            OrderStatusEnum::REFUNDED => 'text-slate-700 bg-slate-100 border-slate-200',
            default => 'text-slate-700 bg-slate-100 border-slate-200',
        };
    }

    /**
     * Resolve payment status label.
     */
    public function latestPaymentLabel(Order $order): string
    {
        $payment = $order->latestPayment;

        if (!$payment) {
            return 'پرداخت ثبت نشده';
        }

        return method_exists($payment->status, 'label')
            ? $payment->status->label()
            : $payment->status->value;
    }

    /**
     * Format money amount.
     */
    public function money(int|string|null $amount): string
    {
        return number_format((int)$amount) . ' تومان';
    }

    /**
     * Format date as Jalali.
     */
    public function dateLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d');
    }

    /**
     * Format datetime as Jalali.
     */
    public function dateTimeLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d H:i');
    }

    /**
     * Resolve main order title.
     */
    public function orderTitle(Order $order): string
    {
        $items = $order->rootItems;

        if ($items->isEmpty()) {
            return 'سفارش بدون آیتم';
        }

        $firstTitle = $this->orderItemTitle($items->first());

        if ($items->count() === 1) {
            return $firstTitle;
        }

        return $firstTitle . ' و ' . ($items->count() - 1) . ' مورد دیگر';
    }

    /**
     * Resolve order item title.
     */
    public function orderItemTitle(?OrderItem $item): string
    {
        if (!$item) {
            return 'آیتم سفارش';
        }

        return data_get($item->snapshot, 'title')
            ?? data_get($item->snapshot, 'product_title')
            ?? data_get($item->snapshot, 'name')
            ?? 'آیتم سفارش';
    }

    /**
     * Resolve order item description.
     */
    public function orderItemDescription(OrderItem $item): ?string
    {
        return data_get($item->snapshot, 'description')
            ?? data_get($item->metadata, 'description')
            ?? null;
    }

    /**
     * Resolve quantity label.
     */
    public function quantityLabel(OrderItem $item): string
    {
        return 'تعداد: ' . number_format((int)$item->quantity);
    }

    /**
     * Determine whether order has child items.
     */
    public function hasChildren(OrderItem $item): bool
    {
        return $item->children->isNotEmpty();
    }

    /**
     * Determine whether order can be retried.
     */
    public function canRetryPayment(Order $order): bool
    {
        return $order->status->canStartPayment();
    }

    /**
     * Determine whether order has downloadable access.
     */
    public function hasDownloads(Order $order): bool
    {
        return $order->purchaseAccesses->isNotEmpty();
    }

    /**
     * Resolve payment retry URL.
     */
    public function retryPaymentUrl(Order $order): string
    {
        $payment = $order->latestPayment;

        if ($payment && Route::has('payment.retry')) {
            return route('payment.retry', $payment->tracking_code);
        }

        return '#';
    }

    /**
     * Resolve invoice URL.
     */
    public function invoiceUrl(Order $order): string
    {
        if (Route::has('orders.invoice')) {
            return route('orders.invoice', $order);
        }

        if (Route::has('front.orders.invoice')) {
            return route('front.orders.invoice', $order);
        }

        return '#';
    }

    /**
     * Determine whether invoice route exists.
     */
    public function hasInvoiceRoute(): bool
    {
        return Route::has('orders.invoice') || Route::has('front.orders.invoice');
    }

    /**
     * Resolve downloads section URL.
     */
    public function downloadsUrl(): string
    {
        if (Route::has('dashboard.downloads')) {
            return route('dashboard.downloads');
        }

        if (Route::has('front.dashboard.downloads')) {
            return route('front.dashboard.downloads');
        }

        return '#downloads';
    }

    /**
     * Render component.
     */
    public function render(): View
    {
        return view('livewire.front.account.my-orders-section');
    }
}
