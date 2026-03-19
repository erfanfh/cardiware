@props([
    'name' => null,
    'value' => '',
    'id' => null,
    'preset' => 'default',
    'height' => 400,
    'dir' => 'rtl',
    'placeholder' => '',
    'readonly' => false,
])

@php
    $inputId = $id ?: ($name
        ? str($name)->replace(['[', ']'], ['-', ''])->trim('-')->slug('_')
        : 'editor_' . str()->random(8));

    $editorId = 'editor_' . $inputId . '_' . str()->random(6);
@endphp

<div
    data-editor-wrapper
    class="w-full"
>
    <textarea
        id="{{ $editorId }}"
        @if($name) name="{{ $name }}" @endif
        placeholder="{{ $placeholder }}"
        dir="{{ $dir }}"
        data-editor
        data-editor-preset="{{ $preset }}"
        data-editor-height="{{ (int) $height }}"
        data-editor-dir="{{ $dir }}"
        data-editor-readonly="{{ $readonly ? 'true' : 'false' }}"
        {{ $attributes->merge([
            'class' => 'w-full min-h-[220px] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm leading-7 text-slate-800 outline-none transition focus:border-orange-300 focus:ring-2 focus:ring-orange-200 placeholder:text-slate-400',
        ]) }}
    >{{ $value }}</textarea>
</div>
