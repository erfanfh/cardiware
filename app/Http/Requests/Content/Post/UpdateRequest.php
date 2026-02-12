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
        $postId = $this->route('post')?->id
            ?? $this->route('post')?->id;

        return [
            ...$this->baseRules(),

            'slug' => [
                'required',
                'max:255',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
        ];
    }
}
