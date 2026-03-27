@props([
    'columns' => 1,
    'sidebarSpan' => 3,
    'sidebarPosition' => 'left'
])

@php
    $sidebarClasses = [
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
    ];

    $contentSpan = 12 - $sidebarSpan;

    $contentClasses = [
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
    ];
@endphp

<div class="grid grid-cols-12 gap-5">
    @if($columns == 2)
        @if($sidebarPosition === 'left')
            {{-- Content --}}
            <div class="col-span-12 {{ $contentClasses[$contentSpan] ?? 'lg:col-span-9' }} space-y-5">
                {{ $slot }}
            </div>

            {{-- Sidebar --}}
            <div class="col-span-12 {{ $sidebarClasses[$sidebarSpan] ?? 'lg:col-span-3' }} space-y-5">
                {{ $sidebar ?? '' }}
            </div>
        @else
            {{-- Sidebar --}}
            <div class="col-span-12 {{ $sidebarClasses[$sidebarSpan] ?? 'lg:col-span-3' }} space-y-5">
                {{ $sidebar ?? '' }}
            </div>

            {{-- Content --}}
            <div class="col-span-12 {{ $contentClasses[$contentSpan] ?? 'lg:col-span-9' }} space-y-5">
                {{ $slot }}
            </div>
        @endif
    @else
        <div class="col-span-12 space-y-5">
            {{ $slot }}
        </div>
    @endif
</div>
