<?php

namespace App\Http\Requests\Content\Post;

use Illuminate\Validation\Rule;

class UpdateRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $slugId = $this->route('post')->slugs()->where('is_current', true)->first()->id;

        return [
            ...$this->baseRules(),

            'slug' => [
                'required',
                'max:255',
                'alpha_dash',
                Rule::unique('content_slugs', 'slug')->ignore($slugId),
            ],
        ];
    }
}
