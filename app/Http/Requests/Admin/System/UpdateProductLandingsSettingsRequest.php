<?php

namespace App\Http\Requests\Admin\System;

use App\Application\Content\Support\ProductLandingContentSchema;
use App\Infrastructure\Services\Setting\Groups\ProductLandingsSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use JsonException;

class UpdateProductLandingsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('setting.product-landings') ?? false;
    }

    public function rules(): array
    {
        return ['payload' => ['required', 'string', 'max:1000000'], 'version' => ['required', 'string', 'size:64']];
    }

    public function content(): array
    {
        try {
            // Decode the form transport; validate every nested field against shipped defaults.
            $content = json_decode($this->validated('payload'), true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages(['payload' => 'محتوای فرم معتبر نیست؛ صفحه را دوباره باز کنید.']);
        }

        $schema = app(ProductLandingContentSchema::class);
        $default = ProductLandingsSettings::defaults()[$this->route('document')];
        $schema->validate($content, $schema->infer([$default]));

        return $content;
    }
}
