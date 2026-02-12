<?php

namespace App\Http\Requests\Content\Post;

class StoreRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->baseRules(),

            'slug' => [
                'required',
                'unique:posts,slug',
                'max:255',
                'alpha_dash',
            ],
        ];
    }

    /**
     * Force draft on create.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['active'] = 0;

        return $data;
    }
}
