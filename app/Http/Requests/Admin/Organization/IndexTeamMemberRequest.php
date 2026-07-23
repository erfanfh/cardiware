<?php

namespace App\Http\Requests\Admin\Organization;

use App\Application\Organization\Listings\TeamMemberListing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'job_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'string',
                'max:255',
            ],

            'created_at_from' => [
                'nullable',
                'date',
            ],

            'created_at_to' => [
                'nullable',
                'date',
                'after_or_equal:created_at_from',
            ],

            'sort' => [
                'nullable',
                'string',
                Rule::in($this->allowedSortKeys()),
            ],

            'direction' => [
                'nullable',
                'string',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_at_from' => $this->normalizeDateToGregorian(
                $this->input('created_at_from')
            ),

            'created_at_to' => $this->normalizeDateToGregorian(
                $this->input('created_at_to')
            ),
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allowedSortKeys(): array
    {
        return array_keys(
            app(TeamMemberListing::class)->sortable()
        );
    }

    protected function normalizeDateToGregorian(
        mixed $value
    ): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        return gregorian_date($value);
    }
}
