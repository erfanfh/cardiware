<?php

namespace App\Http\Requests\Front\Support;

use App\Application\Support\Actions\CanCreateTicketAction;
use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    protected $errorBag = 'createTicket';

    public function authorize(): bool
    {
        return auth()->check()
            && app(CanCreateTicketAction::class)->execute($this->user());
    }

    public function rules(): array
    {
        return [
            'department_id' => [
                'required',
                'integer',
                Rule::exists('ticket_departments', 'id')->where('is_active', true),
            ],

            'product_id' => [
                'nullable',
                'integer',
                Rule::exists('products', 'id'),
            ],

            'title' => ['required', 'string', 'min:3', 'max:160'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'department_id' => 'دپارتمان',
            'product_id' => 'محصول',
            'title' => 'عنوان تیکت',
            'message' => 'متن پیام',
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'لطفاً دپارتمان تیکت را انتخاب کنید.',
            'department_id.integer' => 'دپارتمان انتخاب‌شده معتبر نیست.',
            'department_id.exists' => 'دپارتمان انتخاب‌شده معتبر نیست.',

            'product_id.integer' => 'محصول انتخاب‌شده معتبر نیست.',
            'product_id.exists' => 'محصول انتخاب‌شده معتبر نیست.',

            'title.required' => 'لطفاً عنوان تیکت را وارد کنید.',
            'title.string' => 'عنوان تیکت باید متن معتبر باشد.',
            'title.min' => 'عنوان تیکت باید حداقل :min کاراکتر باشد.',
            'title.max' => 'عنوان تیکت نباید بیشتر از :max کاراکتر باشد.',

            'message.required' => 'لطفاً متن پیام را وارد کنید.',
            'message.string' => 'متن پیام باید متن معتبر باشد.',
            'message.min' => 'متن پیام باید حداقل :min کاراکتر باشد.',
            'message.max' => 'متن پیام نباید بیشتر از :max کاراکتر باشد.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $departmentId = $this->input('department_id');

            if (!$departmentId) {
                return;
            }

            $department = TicketDepartment::query()
                ->active()
                ->find($departmentId);

            if (!$department) {
                return;
            }

            if ($department->requiresProduct() && !$this->filled('product_id')) {
                $validator->errors()->add(
                    'product_id',
                    'برای این دپارتمان انتخاب محصول الزامی است.'
                );
            }

            if (!$department->requiresProduct() && $this->filled('product_id')) {
                $this->merge([
                    'product_id' => null,
                ]);
            }
        });
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            redirect()
                ->back()
                ->withInput()
                ->with('ticket_access_denied', true)
        );
    }
}
