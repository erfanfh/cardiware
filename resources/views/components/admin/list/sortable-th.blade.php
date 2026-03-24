@props([
    'label',
    'key',
    'currentSort' => null,
    'currentDirection' => 'desc',
    'route' => null,
])

@php
    $isActive = $currentSort === $key;

    $nextDirection = 'asc';

    if ($isActive && $currentDirection === 'asc') {
        $nextDirection = 'desc';
    }

    $url = $route ?: request()->fullUrlWithQuery([
        'sort' => $key,
        'direction' => $nextDirection,
    ]);
@endphp

<x-admin.list.th {{ $attributes }}>
    <a
        href="{{ $url }}"
        class="inline-flex items-center gap-2 font-bold text-slate-500 transition hover:text-slate-700"
    >
        <span>{{ $label }}</span>

        <span class="flex flex-col leading-none">
            <svg
                class="h-3 w-3 {{ $isActive && $currentDirection === 'asc' ? 'text-brand-600' : 'text-slate-300' }}"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path d="M10 5l-4 4h8l-4-4z"/>
            </svg>

            <svg
                class="h-3 w-3 -mt-1 {{ $isActive && $currentDirection === 'desc' ? 'text-brand-600' : 'text-slate-300' }}"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path d="M10 15l4-4H6l4 4z"/>
            </svg>
        </span>
    </a>
</x-admin.list.th>
