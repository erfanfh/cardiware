<?php

namespace App\Http\Requests\Admin\Support;

use App\Application\Support\Actions\GetTicketFormProductsAction;
use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $department = TicketDepartment::query()
            ->whereKey($this->integer('department_id'))
            ->where('is_active', true)
            ->first();

        $ticketProducts = app(GetTicketFormProductsAction::class)->execute($this->route('ticket')->user);


        $allowedProductIds = collect($ticketProducts)
            ->pluck('id')
            ->filter()
            ->map(static fn($id) => (int)$id)
            ->values()
            ->all();

        return [
            'department_id' => [
                'required',
                'integer',
                Rule::exists(TicketDepartment::class, 'id')
                    ->where('is_active', true),
            ],

            'product_id' => [
                Rule::requiredIf(
                    (bool)$department?->requires_product
                ),
                'nullable',
                'integer',
                Rule::in($allowedProductIds),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'انتخاب دپارتمان الزامی است.',
            'department_id.exists' => 'دپارتمان انتخاب‌شده معتبر یا فعال نیست.',

            'product_id.required' => 'برای دپارتمان انتخاب‌شده، تعیین محصول الزامی است.',
            'product_id.integer' => 'محصول انتخاب‌شده معتبر نیست.',
            'product_id.in' => 'محصول انتخاب‌شده در فهرست محصولات مجاز وجود ندارد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'department_id' => 'دپارتمان',
            'product_id' => 'محصول',
        ];
    }
}
