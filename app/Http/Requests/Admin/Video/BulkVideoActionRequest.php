<?php

namespace App\Http\Requests\Admin\Video;

use App\Infrastructure\Support\BulkActions\DefaultBulkActions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkVideoActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return match ($this->input('bulk_action')) {
            'activate', 'deactivate' => Gate::allows('video.edit'),
            'delete' => Gate::allows('video.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'bulk_action' => ['required', 'string', Rule::in(DefaultBulkActions::set()->values())],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:videos,id'],
        ];
    }

    public function videoIds(): array
    {
        return array_map('intval', $this->input('ids', []));
    }

    public function bulkAction(): string
    {
        return (string) $this->string('bulk_action')->value();
    }
}
