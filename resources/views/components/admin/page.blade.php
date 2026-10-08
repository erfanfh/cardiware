@props([
    'columns' => 1,
    'sidebarSpan' => 3,
    'sidebarPosition' => 'left'
])

@php
    $sidebarClasses = [
        1 => 'xl:col-span-1',
        2 => 'xl:col-span-2',
        3 => 'xl:col-span-3',
        4 => 'xl:col-span-4',
        5 => 'xl:col-span-5',
        6 => 'xl:col-span-6',
        7 => 'xl:col-span-7',
        8 => 'xl:col-span-8',
        9 => 'xl:col-span-9',
        10 => 'xl:col-span-10',
        11 => 'xl:col-span-11',
    ];

    $contentSpan = 12 - $sidebarSpan;

    $contentClasses = [
        1 => 'xl:col-span-1',
        2 => 'xl:col-span-2',
        3 => 'xl:col-span-3',
        4 => 'xl:col-span-4',
        5 => 'xl:col-span-5',
        6 => 'xl:col-span-6',
        7 => 'xl:col-span-7',
        8 => 'xl:col-span-8',
        9 => 'xl:col-span-9',
        10 => 'xl:col-span-10',
        11 => 'xl:col-span-11',
    ];
@endphp

<div class="grid min-w-0 grid-cols-12 gap-5">
    @if($columns == 2)
        @if($sidebarPosition === 'left')
            {{-- Content --}}
            <div class="col-span-12 min-w-0 {{ $contentClasses[$contentSpan] ?? 'xl:col-span-9' }} space-y-5">
                {{ $slot }}
            </div>

            {{-- Sidebar --}}
            <div class="col-span-12 min-w-0 {{ $sidebarClasses[$sidebarSpan] ?? 'xl:col-span-3' }} space-y-5">
                {{ $sidebar ?? '' }}
            </div>
        @else
            {{-- Sidebar --}}
            <div class="col-span-12 min-w-0 {{ $sidebarClasses[$sidebarSpan] ?? 'xl:col-span-3' }} space-y-5">
                {{ $sidebar ?? '' }}
            </div>

            {{-- Content --}}
            <div class="col-span-12 min-w-0 {{ $contentClasses[$contentSpan] ?? 'xl:col-span-9' }} space-y-5">
                {{ $slot }}
            </div>
        @endif
    @else
        <div class="col-span-12 min-w-0 space-y-5">
            {{ $slot }}
        </div>
    @endif
</div>
