@props([
    'name' => null,
    'value' => '',
    'id' => null,
    'label' => null,
    'hint' => null,
    'preset' => 'default',
    'height' => 400,
    'dir' => 'rtl',
    'placeholder' => '',
    'readonly' => false,
    'toolbarSticky' => true,
])

@php
    use Illuminate\Support\Str;

    $normalizedName = $name
        ? str_replace(['][', '[', ']'], ['_', '_', ''], $name)
        : null;

    $baseId = $id ?: ($normalizedName
        ? Str::of($normalizedName)->trim('_')->slug('_')->value()
        : 'editor_' . Str::random(8));

    $editorId = 'editor_' . $baseId . '_' . Str::random(6);

    // برای اتصال مستقیم به mfip
    $editorKey = 'editor-' . Str::random(12);

    $error = $name ? $errors->first($name) : null;
@endphp

<div class="w-full space-y-2" data-editor-wrapper>
    @if($label)
        <label for="{{ $editorId }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <textarea

            id="{{ $editorId }}"
            @if($name) name="{{ $name }}" @endif
            placeholder="{{ $placeholder }}"
            dir="{{ $dir }}"
            data-editor
            data-editor-key="{{ $editorKey }}"
            data-editor-preset="{{ $preset }}"
            data-editor-height="{{ (int) $height }}"
            data-editor-dir="{{ $dir }}"
            data-editor-readonly="{{ $readonly ? 'true' : 'false' }}"
            data-editor-toolbar-sticky="{{ $toolbarSticky ? 'true' : 'false' }}"
            {{ $attributes->merge([
                'class' => 'w-full min-h-[220px] rounded-lg border bg-white px-3 py-2 text-sm leading-7 text-slate-800 outline-none transition placeholder:text-slate-400 ' .
                    ($error
                        ? 'border-red-300 focus:border-red-400 focus:ring-2 focus:ring-red-100'
                        : 'border-slate-200 focus:border-orange-300 focus:ring-2 focus:ring-orange-200'),
            ]) }}
        >{{ $value }}</textarea>
    </div>

    @if($hint && !$error)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endif
</div>
