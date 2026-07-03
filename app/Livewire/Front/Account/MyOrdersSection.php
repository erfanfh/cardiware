<?php

namespace App\Livewire\Front\Account;

use App\Application\Commerce\Actions\Order\GetUserOrdersAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;

class MyOrdersSection extends Component
{
    use WithPagination;

    public int $perPage = 5;

    /**
     * Get current user's orders.
     */
    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        if (!auth()->check()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }

        return app(GetUserOrdersAction::class)->execute(
            userId: (int)auth()->id(),
            perPage: $this->perPage,
        );
    }

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

    public function orderStatusClass(Order $order): string
    {
        return match ($order->status) {
            OrderStatusEnum::PAID => '
            border border-emerald-200
            bg-emerald-50
            text-emerald-700
            dark:border-emerald-500/20
            dark:bg-emerald-500/10
            dark:text-emerald-300
        ',

            OrderStatusEnum::PENDING_PAYMENT => '
            border border-amber-200
            bg-amber-50
            text-amber-700
            dark:border-amber-500/20
            dark:bg-amber-500/10
            dark:text-amber-300
        ',

            OrderStatusEnum::FAILED,
            OrderStatusEnum::CANCELLED => '
            border border-red-200
            bg-red-50
            text-red-700
            dark:border-red-500/20
            dark:bg-red-500/10
            dark:text-red-300
        ',

            OrderStatusEnum::REFUNDED => '
            border border-slate-200
            bg-slate-100
            text-slate-700
            dark:border-slate-700
            dark:bg-slate-800
            dark:text-slate-300
        ',

            default => '
            border border-slate-200
            bg-slate-100
            text-slate-700
            dark:border-slate-700
            dark:bg-slate-800
            dark:text-slate-300
        ',
        };
    }

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

    public function money(int|string|null $amount): string
    {
        return number_format((int)$amount) . ' تومان';
    }

    public function dateLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d');
    }

    public function dateTimeLabel($date): string
    {
        if (!$date) {
            return '—';
        }

        return Jalalian::fromDateTime($date)->format('Y/m/d H:i');
    }

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

    public function orderItemDescription(OrderItem $item): ?string
    {
        return data_get($item->snapshot, 'description')
            ?? data_get($item->metadata, 'description')
            ?? null;
    }

    public function quantityLabel(OrderItem $item): string
    {
        return 'تعداد: ' . number_format((int)$item->quantity);
    }

    public function hasChildren(OrderItem $item): bool
    {
        return $item->children->isNotEmpty();
    }

    public function canRetryPayment(Order $order): bool
    {
        return $order->status->canStartPayment();
    }

    public function hasDownloads(Order $order): bool
    {
        return $order->purchaseAccesses->isNotEmpty();
    }

    public function invoiceUrl(Order $order): string
    {
        return '#';
    }

    public function hasInvoiceRoute(): bool
    {
        return false;
    }

    public function downloadsUrl(): string
    {
        return route('account.downloads');
    }

    public function render(): View
    {
        return view('livewire.front.account.my-orders-section');
    }
}
