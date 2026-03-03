@props([
    'variant' => 'solid', // solid|soft|danger|ghost
    'size' => 'md',       // sm|md
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-bold transition rounded-xl select-none';
    $sizes = [
        'sm' => 'h-9 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
    ];
    $variants = [
        'solid'  => 'bg-orange-500 text-white hover:bg-orange-600',
        'soft'   => 'bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100',
        'danger' => 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100',
        'ghost'  => 'text-slate-700 hover:bg-slate-100',
    ];
@endphp

<button {{ $attributes->merge(['class' => $base.' '.$sizes[$size].' '.$variants[$variant]]) }}>
    {{ $slot }}
</button>
