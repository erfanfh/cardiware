<?php

namespace App\Application\Commerce\DTOs;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Financial\Enums\PaymentStatusEnum;

final readonly class OrderReceiptData
{
    public function __construct(
        public string $orderNumber,
        public string $customerName,
        public string $paidAt,
        public string $paymentMethod,
        public ?string $trackingCode,
        public array $items,
        public int $subtotal,
        public int $productDiscount,
        public int $couponDiscount,
        public array $couponCodes,
        public int $tax,
        public string $taxRate,
        public int $total,
        public string $ordersUrl,
    ) {}

    public static function fromOrder(Order $order): self
    {
        $order->loadMissing(['user', 'items']);
        $payment = $order->payments()->where('status', PaymentStatusEnum::PAID)->first();
        $name = trim((string) ($order->user?->display_name ?: $order->user?->full_name ?: $order->user?->username));

        // Use the order's immutable purchase snapshots, never current catalog prices or titles.
        $title = static fn (OrderItem $item): string => (string) (
            data_get($item->snapshot, 'title')
            ?: data_get($item->snapshot, 'product_title')
            ?: data_get($item->snapshot, 'plan_title')
            ?: 'قلم سفارش'
        );

        return new self(
            orderNumber: (string) $order->order_number,
            customerName: $name ?: 'کاربر عزیز',
            paidAt: $order->paid_at ? jalali_date($order->paid_at->copy()->setTimezone('Asia/Tehran'), 'Y/m/d H:i') : '—',
            paymentMethod: $payment?->gateway->label() ?? 'تأیید پرداخت سفارش',
            trackingCode: $payment?->tracking_code,
            items: $order->items->map(fn (OrderItem $item): array => [
                'title' => $title($item),
                'parent_title' => ($parent = $order->items->firstWhere('id', $item->parent_order_item_id)) ? $title($parent) : null,
                'quantity' => (int) $item->quantity,
                'unit_price' => (int) $item->unit_price,
                'discount' => (int) $item->discount_amount + (int) $item->coupon_discount_amount,
                'total' => (int) $item->final_total_amount,
            ])->all(),
            subtotal: (int) $order->subtotal_amount,
            productDiscount: max(0, (int) $order->discount_amount - (int) $order->coupon_discount_amount),
            couponDiscount: (int) $order->coupon_discount_amount,
            couponCodes: array_values(array_filter(array_column($order->appliedCouponSnapshots(), 'code'))),
            tax: (int) $order->tax_amount,
            taxRate: rtrim(rtrim(number_format((int) $order->tax_rate_basis_points / 100, 2, '.', ''), '0'), '.'),
            total: (int) $order->total_amount,
            ordersUrl: route('account.orders'),
        );
    }

    public function money(int $amount): string
    {
        return number_format($amount).' تومان';
    }
}
