@props([
    'value' => 0,
])

@php
    $value = max(0, min(100, (int) $value));

    $color = match (true) {
        $value >= 100 => 'bg-emerald-500',
        $value >= 70 => 'bg-brand-500',
        $value >= 40 => 'bg-sky-500',
        $value >= 1 => 'bg-amber-500',
        default => 'bg-slate-300',
    };
@endphp

<div class="min-w-[120px]">
    <div class="mb-1 flex items-center justify-between text-[11px] text-slate-500">
        <span>پیشرفت</span>
        <span class="font-bold text-slate-700">{{ $value }}%</span>
    </div>

    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
        <div class="h-full rounded-full transition-all duration-300 {{ $color }}" style="width: {{ $value }}%"></div>
    </div>
</div>
