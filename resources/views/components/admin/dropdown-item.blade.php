@props([
    'variant' => 'default',
    'tag' => 'a',
])

@php
    $classes = match ($variant) {
        'danger' => 'text-rose-600 hover:bg-rose-50',
        default => 'text-slate-700 hover:bg-slate-50',
    };
@endphp

@if($tag === 'button')
    <button
        type="button"
        {{ $attributes->merge([
            'class' => 'flex w-full items-center justify-between gap-3 px-4 py-2.5 text-sm transition ' . $classes
        ]) }}
    >
        {{ $slot }}
    </button>
@else
    <a
        {{ $attributes->merge([
            'class' => 'flex w-full items-center justify-between gap-3 px-4 py-2.5 text-sm transition ' . $classes
        ]) }}
    >
        {{ $slot }}
    </a>
@endif
