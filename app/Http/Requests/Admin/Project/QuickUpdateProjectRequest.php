<?php

namespace App\Http\Requests\Admin\Project;

use App\Domain\Project\Enums\ProjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuickUpdateProjectRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'slug')->ignore($this->project?->id),
            ],
            'status' => [
                'required',
                Rule::in(array_column(ProjectStatusEnum::cases(), 'value')),
            ],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'tags' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان پروژه',
            'slug' => 'اسلاگ',
            'status' => 'وضعیت',
            'progress_percent' => 'درصد پیشرفت',
            'tags' => 'برچسب‌ها',
        ];
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim($this->title) : $this->title,
            'tags' => is_string($this->tags) ? trim($this->tags) : $this->tags,
            'progress_percent' => $this->progress_percent === '' ? null : $this->progress_percent,
        ]);
    }
}
