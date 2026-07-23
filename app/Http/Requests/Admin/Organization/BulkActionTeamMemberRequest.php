<?php

namespace App\Http\Requests\Admin\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BulkActionTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->input('action');

        if (!$action) {
            return false;
        }

        return match ($action) {
            'delete' => Gate::allows('team-member.delete'),
            default => false,
        };
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                'string',
                Rule::in([
                    'delete',
                ]),
            ],

            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('team_members', 'id')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'action' => 'عملیات گروهی',
            'ids' => 'اعضای انتخاب شده',
            'ids.*' => 'عضو انتخاب شده',
        ];
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->input('ids', []);

        if (!is_array($ids)) {
            $ids = [];
        }

        $this->merge([
            'ids' => collect($ids)
                ->filter(fn(mixed $id) => filled($id))
                ->map(fn(mixed $id) => (int)$id)
                ->unique()
                ->values()
                ->all(),
        ]);
    }
}
