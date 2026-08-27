<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class RevealContentKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // EnsureUserIsAdmin already gates the whole admin.* route group; the real
        // authorization here is the separate vault password checked in the action.
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sale_item_id' => ['required', 'integer', 'exists:product_sale_items,id'],
            'vault_password' => ['required', 'string', 'max:256'],
        ];
    }
}
