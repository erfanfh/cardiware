@props([
    'title',
    'subtitle' => null,
    'backRoute' => null,
    'backLabel' => 'بازگشت',
    'showPost' => null,
])

@php
    $hasAside = isset($aside) && trim($aside) !== '';
@endphp

<div class="space-y-6">
    {{-- هدر صفحه --}}
    <div class="flex flex-col md:flex-row items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h1>

            @if($subtitle)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @if($backRoute)
            <div class="flex items-center gap-2">
                <a href="{{ $backRoute }}" class="inline-flex items-center justify-center py-[7px] px-[14px] gap-[6px] rounded-full text-[0.75rem] font-normal transition text-[var(--text-main)] dark:text-gray-100 border border-[var(--border-strong)] dark:border-gray-700 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-700">
                    {{ $backLabel }}
                </a>
                @if($showPost)
                    <a href="{{ $showPost }}" class="px-4 py-2 rounded-full bg-gradient-to-r from-orange-500 to-orange-500 text-white text-sm font-medium shadow-lg dark:shadow-md hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        مشاهده دوره
                    </a>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-6  grid-cols-1 lg:grid-cols-[minmax(0,2.5fr)_minmax(200px,1fr)]">
        <div class="w-full order-2 lg:order-1 mb-0 md:mb-12">
            {{ $slot }}
        </div>

        @if($hasAside)
            <aside class="w-full order-1 lg:order-2">
                {{ $aside }}
            </aside>
        @endif
    </div>

</div>
