<?php

namespace App\Http\Requests\Admin\Commerce;

use App\Domain\Commerce\Enums\OrderStatusEnum;
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
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'وضعیت سفارش',
        ];
    }
}
