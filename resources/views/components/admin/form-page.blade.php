@props([
    'title',
    'subtitle' => null,
    'backRoute' => null,
    'backLabel' => 'بازگشت',
    'showCourse' => null,
])

@php
    $hasAside = isset($aside) && trim($aside) !== '';
@endphp

<div class="space-y-6">
    {{-- هدر صفحه --}}
    <div class="flex items-start justify-between gap-4 mb-6">
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
                <a href="{{ $backRoute }}" class="px-4 py-2 rounded-full border border-gray-300 dark:border-gray-600 bg-transparent text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    {{ $backLabel }}
                </a>

                @if($showCourse)
                    <a href="{{ $showCourse }}" class="px-4 py-2 rounded-full bg-gradient-to-r from-orange-500 to-orange-500 text-white text-sm font-medium shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        مشاهده دوره
                    </a>
                @endif
            </div>
        @endif
    </div>

    {{-- بدنه صفحه --}}
    <div class="grid gap-6 {{ $hasAside ? 'lg:grid-cols-3' : '' }}">
        {{-- ستون اصلی فرم --}}
        <div class="{{ $hasAside ? 'lg:col-span-2' : '' }}">
            {{ $slot }}
        </div>

        {{-- ستون ساید (اختیاری) --}}
        @if($hasAside)
            <aside class="{{ $hasAside ? 'lg:col-span-1 sticky top-6 self-start' : '' }}">
                {{ $aside }}
            </aside>
        @endif
    </div>
</div>
