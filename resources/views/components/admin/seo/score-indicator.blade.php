@php
    $tooltip = $score === null
        ? 'برای این مورد هنوز تحلیل سئو انجام نشده است'
        : "وضعیت سئو: {$label}";
@endphp

<div
    x-data="{ open: false }"
    class="relative inline-flex items-center justify-center w-full text-center"
>
    <button
        type="button"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
        x-on:focus="open = true"
        x-on:blur="open = false"
        class="cursor-help relative flex h-4 w-4 items-center justify-center rounded-full outline-none"
        aria-label="{{ $tooltip }}"
    >
        <span
            class="absolute inset-0 rounded-full {{ $color }} opacity-20"
        ></span>

        <span
            @class([
                'relative h-3.5 w-3.5 rounded-full ring-4',
                $color,
                $ring,
                'animate-pulse' => $pulse,
            ])
        ></span>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.opacity.scale.duration.150ms
        class="pointer-events-none absolute bottom-full z-50 mb-2 whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-xl"
    >
        {{ $tooltip }}

        @if($score !== null)
            <div class="mt-1 text-[11px] text-slate-500">
                امتیاز داخلی: {{ $score }}
            </div>
        @endif

        <div
            class="absolute left-1/2 top-full h-2 w-2 -translate-x-1/2 rotate-45 border-b border-r border-slate-200 bg-white"></div>
    </div>
</div>
