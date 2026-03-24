@props([
    'color' => 'slate',
    'size' => 'sm',
])

@php
    $map = [
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'green' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'yellow' => 'bg-amber-100 text-amber-700 ring-amber-200',
        'red' => 'bg-rose-100 text-rose-700 ring-rose-200',
        'blue' => 'bg-sky-100 text-sky-700 ring-sky-200',
        'brand' => 'bg-brand-100 text-brand-700 ring-brand-200',
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    ];

    $sizes = [
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1 rounded-full font-medium ring-1 ring-inset ' . ($map[$color] ?? $map['slate']) . ' ' . ($sizes[$size] ?? $sizes['sm'])
]) }}>
    {{ $slot }}
</span>
