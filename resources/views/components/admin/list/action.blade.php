@props([
    'tag' => 'a', // a | button
    'type' => 'button',
    'variant' => 'default', // default | primary | danger
])

@php
    $classes = match ($variant) {
        'primary' => 'text-brand-600 hover:text-brand-700',
        'danger' => 'text-rose-600 hover:text-rose-700',
        default => 'text-slate-600 hover:text-slate-900',
    };
@endphp

@if($tag === 'button')
    <button
        type="{{ $type }}"
        {{ $attributes->merge([
            'class' => 'font-medium transition cursor-pointer ' . $classes
        ]) }}
    >
        {{ $slot }}
    </button>
@else
    <a
        {{ $attributes->merge([
            'class' => 'font-medium transition ' . $classes
        ]) }}
    >
        {{ $slot }}
    </a>
@endif
