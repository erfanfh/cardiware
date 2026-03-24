@props([
    'tag' => 'button', // button | a
    'type' => 'button',
    'href' => null,
    'variant' => 'solid', // solid|soft|danger|ghost|secondary|dark
    'size' => 'md',       // sm|md|lg
    'block' => false,
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl select-none transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:pointer-events-none disabled:opacity-60';

    $sizes = [
        'sm' => 'h-9 px-3 text-xs font-bold',
        'md' => 'h-10 px-4 text-sm font-bold',
        'lg' => 'h-11 px-5 text-sm font-bold',
    ];

    $variants = [
        // backward compatible variants
        'solid' => 'bg-orange-500 text-white hover:bg-orange-600 focus:ring-orange-200 shadow-sm',
        'soft' => 'bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100 focus:ring-orange-200',
        'danger' => 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 focus:ring-rose-200',
        'ghost' => 'text-slate-700 hover:bg-slate-100 focus:ring-slate-200',

        // new variants
        'secondary' => 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 focus:ring-slate-200',
        'dark' => 'bg-slate-900 text-white hover:bg-slate-800 focus:ring-slate-300 shadow-sm',
    ];

    $classes = implode(' ', array_filter([
        $base,
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['solid'],
        $block ? 'w-full' : '',
    ]));
@endphp

@if($tag === 'a')
    <a
        href="{{ $disabled ? 'javascript:void(0)' : $href }}"
        @if($disabled) aria-disabled="true" @endif
        {{ $attributes->merge([
            'class' => $classes,
        ]) }}
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->merge([
            'class' => $classes,
        ]) }}
    >
        {{ $slot }}
    </button>
@endif
