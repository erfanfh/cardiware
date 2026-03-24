<?php

namespace App\Http\Requests\Admin\Layout;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageLayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'layout_state' => ['required', 'string'],
            'elements' => ['nullable', 'array'],
            'elements.*' => ['array'],
            'elements.*.is_active' => ['nullable'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function layoutState(): array
    {
        $decoded = json_decode($this->input('layout_state', '{}'), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function elementStates(): array
    {
        $elements = $this->input('elements', []);

        return is_array($elements) ? $elements : [];
    }
}
