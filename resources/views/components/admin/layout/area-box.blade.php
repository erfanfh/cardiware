@props([
    'areaKey',
    'title',
    'description' => null,
    'count' => 0,
    'empty' => false,
    'width' => 12,
])

@php
    $spanClass = match ((int) $width) {
        1 => 'lg:col-span-1',
        2 => 'lg:col-span-2',
        3 => 'lg:col-span-3',
        4 => 'lg:col-span-4',
        5 => 'lg:col-span-5',
        6 => 'lg:col-span-6',
        7 => 'lg:col-span-7',
        8 => 'lg:col-span-8',
        9 => 'lg:col-span-9',
        10 => 'lg:col-span-10',
        11 => 'lg:col-span-11',
        default => 'lg:col-span-12',
    };
@endphp

<section
    @class([
        'h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors duration-200 flex flex-col',
        $spanClass,
    ])
>
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
        <div>
            <h2 class="text-sm font-bold text-slate-800">{{ $title }}</h2>

            @if($description)
                <p class="mt-1 text-xs text-slate-500">{{ $description }}</p>
            @endif
        </div>

        <div
            data-layout-area-count
            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600"
        >
            {{ $count }} المنت
        </div>
    </div>

    <div
        data-layout-area
        data-area-key="{{ $areaKey }}"
        data-sortable-group="layout-elements"
        class="flex-1 rounded-b-2xl border-2 border-dashed border-transparent bg-slate-50/70 p-4 transition-all duration-200 hover:border-slate-200"
    >
        <div class="relative h-full min-h-14">
            <div
                data-layout-empty
                class="{{ $empty ? '' : 'hidden' }} pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white/70 px-4 text-center text-xs text-slate-400"
            >
                هنوز المنتی در این باکس قرار نگرفته است
            </div>

            <div
                data-layout-elements-list
                class="min-h-full space-y-3"
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</section>
