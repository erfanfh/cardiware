@props([
    'label' => null,
    'align' => 'right',
    'width' => null,

    'sortable' => false,
    'sortKey' => null,
    'currentSort' => null,
    'currentDirection' => 'desc',

    /**
     * Modes:
     * - null      => auto detect
     * - 'link'    => classic query-string links
     * - 'livewire'=> Livewire action buttons
     */
    'sortMode' => null,

    /**
     * Only used when sortMode = livewire
     */
    'sortAction' => 'sortByColumn',

    /**
     * Only used when sortMode = link
     */
    'sortParam' => 'sort',
    'directionParam' => 'direction',
])

@php
    $labelText = $label ?? trim((string) $slot);

    $normalizedDirection = strtolower((string) $currentDirection) === 'asc' ? 'asc' : 'desc';
    $isActive = $sortable && filled($sortKey) && $currentSort === $sortKey;
    $isAscActive = $isActive && $normalizedDirection === 'asc';
    $isDescActive = $isActive && $normalizedDirection === 'desc';

    $resolvedSortMode = $sortMode;

    if ($resolvedSortMode === null) {
        $resolvedSortMode = $attributes->has('wire:click') || $attributes->has('wire:target')
            ? 'livewire'
            : 'link';
    }

    $wrapperClass = match ($align) {
        'center' => 'justify-center',
        'left' => 'justify-end',
        default => 'justify-start',
    };

    $textAlignClass = match ($align) {
        'center' => 'text-center',
        'left' => 'text-left',
        default => 'text-right',
    };

    $baseQuery = request()->query();
    unset($baseQuery[$sortParam], $baseQuery[$directionParam]);

    $buildSortUrl = function (string $direction, bool $isActiveDirection) use ($baseQuery, $sortKey, $sortParam, $directionParam) {
        if ($isActiveDirection) {
            $query = $baseQuery;
        } else {
            $query = array_merge($baseQuery, [
                $sortParam => $sortKey,
                $directionParam => $direction,
            ]);
        }

        return url()->current() . (count($query) ? '?' . http_build_query($query) : '');
    };

    $ascUrl = filled($sortKey) ? $buildSortUrl('asc', $isAscActive) : '#';
    $descUrl = filled($sortKey) ? $buildSortUrl('desc', $isDescActive) : '#';

    $buttonBaseClass = 'cursor-pointer flex h-6 w-6 items-center justify-center rounded-md transition';
    $buttonIdleClass = 'text-slate-400 hover:bg-slate-100 hover:text-slate-600';
    $buttonActiveClass = 'text-brand-600';

    $ascButtonClass = $buttonBaseClass . ' ' . ($isAscActive ? $buttonActiveClass : $buttonIdleClass);
    $descButtonClass = $buttonBaseClass . ' ' . ($isDescActive ? $buttonActiveClass : $buttonIdleClass);
@endphp

<th
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->except(['wire:click', 'wire:target'])->merge([
        'class' => 'whitespace-nowrap px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500'
    ]) }}
>
    @if($sortable && filled($sortKey))
        <div class="flex items-center gap-2 {{ $wrapperClass }}">
            <span class="{{ $textAlignClass }}">
                {{ $label ?? $slot }}
            </span>

            <span class="inline-flex items-center gap-0.5 rounded-md">
                @if($resolvedSortMode === 'livewire')
                    <button
                        type="button"
                        wire:click="{{ $sortAction }}('{{ $sortKey }}', 'asc')"
                        class="{{ $ascButtonClass }}"
                        title="{{ $isAscActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی صعودی' }}"
                        aria-label="{{ $isAscActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی صعودی' }} {{ $labelText }}"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M10 6.25a.75.75 0 0 1 .53.22l3.5 3.5a.75.75 0 1 1-1.06 1.06L10 8.06l-2.97 2.97a.75.75 0 0 1-1.06-1.06l3.5-3.5A.75.75 0 0 1 10 6.25Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>

                    <button
                        type="button"
                        wire:click="{{ $sortAction }}('{{ $sortKey }}', 'desc')"
                        class="{{ $descButtonClass }}"
                        title="{{ $isDescActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی نزولی' }}"
                        aria-label="{{ $isDescActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی نزولی' }} {{ $labelText }}"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M10 13.75a.75.75 0 0 1-.53-.22l-3.5-3.5a.75.75 0 0 1 1.06-1.06L10 11.94l2.97-2.97a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-.53.22Z"
                                  clip-rule="evenodd"/>
                        </svg>
                    </button>
                @else
                    <a
                        href="{{ $ascUrl }}"
                        class="{{ $ascButtonClass }}"
                        title="{{ $isAscActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی صعودی' }}"
                        aria-label="{{ $isAscActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی صعودی' }} {{ $labelText }}"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 6.25a.75.75 0 0 1 .53.22l3.5 3.5a.75.75 0 1 1-1.06 1.06L10 8.06l-2.97 2.97a.75.75 0 0 1-1.06-1.06l3.5-3.5A.75.75 0 0 1 10 6.25Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </a>

                    <a
                        href="{{ $descUrl }}"
                        class="{{ $descButtonClass }}"
                        title="{{ $isDescActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی نزولی' }}"
                        aria-label="{{ $isDescActive ? 'حذف مرتب‌سازی' : 'مرتب‌سازی نزولی' }} {{ $labelText }}"
                    >
                        <svg
                            class="h-3.5 w-3.5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 13.75a.75.75 0 0 1-.53-.22l-3.5-3.5a.75.75 0 0 1 1.06-1.06L10 11.94l2.97-2.97a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-.53.22Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </a>
                @endif
            </span>
        </div>
    @else
        <div class="{{ $textAlignClass }}">
            {{ $label ?? $slot }}
        </div>
    @endif
</th>
