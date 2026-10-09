<?php

namespace App\Http\Requests\Admin\Career;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCareerPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $position = $this->route('career_position');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\pN_-]+$/u', Rule::unique('career_positions', 'slug')->ignore($position?->id)],
            'department' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'cooperation_type' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:100'],
            'detail_label' => ['nullable', 'string', 'max:100'],
            'detail_value' => ['nullable', 'string', 'max:200'],
            'summary' => ['required', 'string', 'max:5000'],
            'requirements_text' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
            'published_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'published_at' => $this->input('published_at')
                ? gregorian_date($this->input('published_at'))
                : now()->format('Y-m-d H:i:s'),
        ]);
    }
}
