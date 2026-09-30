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
                Rule::requiredIf(fn (): bool => $this->input('status') === OrderStatusEnum::REFUNDED->value
                    && $this->route('order')?->status === OrderStatusEnum::PAID
                    && (int) $this->route('order')->getPayableAmount() > 0),
                'nullable',
                Rule::enum(OrderRefundMethodEnum::class),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'وضعیت سفارش',
            'refund_method' => 'روش استرداد',
        ];
    }
}
