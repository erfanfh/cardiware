@props([
    'src' => null,
    'alt' => '',
    'emptyText' => 'بدون تصویر',
    'size' => 'md', // sm | md | lg
])

@php
    $sizes = [
        'sm' => 'h-14 w-16',
        'sm-square' => 'h-14 w-14',
        'md' => 'h-18 w-20',
        'sm-square' => 'h-18 w-18',
        'lg' => 'h-24 w-28',
    ];
@endphp

<div @class([
    'overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm',
    $sizes[$size] ?? $sizes['md'],
])>
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            class="h-full w-full object-cover"
        >
    @else
        <div class="flex h-full w-full items-center justify-center text-xs text-slate-400">
            {{ $emptyText }}
        </div>
    @endif
</div>
