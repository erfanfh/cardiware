<?php

namespace App\Http\Requests\Admin\Commerce;

use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Enums\OrderRefundMethodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(OrderStatusEnum::class)],
            'refund_method' => [
                Rule::requiredIf(fn (): bool => $this->isRefundingPaidOrder()
                    && ((int) $this->route('order')->getPayableAmount() > 0 || $this->selectedRelatedModOrderIds() !== [])),
                'nullable',
                Rule::enum(OrderRefundMethodEnum::class),
            ],
            // مبلغ دلخواه استرداد؛ خالی = کل مبلغ پرداختی.
            'refund_amount' => [
                'nullable',
                'integer',
                'min:0',
                'max:' . max(0, (int) $this->route('order')?->getPayableAmount()),
            ],
            // سفارش‌های ماد مرتبط که همراه این سفارش مرجوع می‌شوند (کلید = شناسه سفارش).
            'related_mod_orders' => ['nullable', 'array'],
            'related_mod_orders.*.refund' => ['nullable', 'boolean'],
            'related_mod_orders.*.amount' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function isRefundingPaidOrder(): bool
    {
        return $this->input('status') === OrderStatusEnum::REFUNDED->value
            && $this->route('order')?->status === OrderStatusEnum::PAID;
    }

    /**
     * شناسه‌ی سفارش‌های ماد که ادمین تیک مرجوعی‌شان را زده است.
     *
     * @return list<int>
     */
    public function selectedRelatedModOrderIds(): array
    {
        if (! $this->isRefundingPaidOrder()) {
            return [];
        }

        return collect((array) $this->input('related_mod_orders', []))
            ->filter(static fn($row): bool => is_array($row) && filter_var($row['refund'] ?? false, FILTER_VALIDATE_BOOLEAN))
            ->keys()
            ->map(static fn($id): int => (int) $id)
            ->filter(static fn(int $id): bool => $id > 0)
            ->values()
            ->all();
    }

    public function attributes(): array
    {
        return [
            'status' => 'وضعیت سفارش',
            'refund_method' => 'روش استرداد',
            'refund_amount' => 'مبلغ استرداد',
            'related_mod_orders.*.amount' => 'مبلغ استرداد ماد',
        ];
    }
}
