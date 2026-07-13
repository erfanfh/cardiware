<?php

namespace App\Http\Requests\Api\V1\Catalog;

use App\Domain\Catalog\Enums\ProductStructureEnum;
use App\Domain\Catalog\Enums\ProductTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'category' => [
                'nullable',
                'string',
                'max:255',
            ],

            'tag' => [
                'nullable',
                'string',
                'max:255',
            ],

            'type' => [
                'nullable',
                Rule::enum(ProductTypeEnum::class),
            ],

            'structure' => [
                'nullable',
                Rule::enum(ProductStructureEnum::class),
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'latest',
                    'oldest',
                    'title_asc',
                    'title_desc',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort' => $this->input('sort', 'latest'),
        ]);
    }

    public function perPage(): int
    {
        return (int)$this->validated('per_page', 20);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'Search products by title, product SKU, sale item title, or sale item SKU.',
                'example' => 'PGL',
            ],

            'category' => [
                'description' => 'Filter products by category path.',
                'example' => 'football-games',
            ],

            'tag' => [
                'description' => 'Filter products by tag path.',
                'example' => 'pes-2026',
            ],

            'type' => [
                'description' => 'Filter by product type.',
                'example' => 'digital',
            ],

            'structure' => [
                'description' => 'Filter by product structure.',
                'example' => 'variable',
            ],

            'sort' => [
                'description' => 'Sort products. Available values: latest, oldest, title_asc, title_desc.',
                'example' => 'latest',
            ],

            'per_page' => [
                'description' => 'Number of products per page. Maximum: 50.',
                'example' => 20,
            ],
        ];
    }
}
