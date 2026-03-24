@props([
    'label' => null,
    'align' => 'right',
    'width' => null,

    'sortable' => false,
    'sortKey' => null,
    'currentSort' => null,
    'currentDirection' => 'desc',
])

@php
    $isActive = $sortable && $currentSort === $sortKey;

    $ascUrl = request()->fullUrlWithQuery([
        'sort' => $sortKey,
        'direction' => 'asc',
    ]);

    $descUrl = request()->fullUrlWithQuery([
        'sort' => $sortKey,
        'direction' => 'desc',
    ]);

    $wrapperClass = match ($align) {
        'center' => 'justify-center',
        'left' => 'justify-start',
        default => 'justify-end',
    };

    $textAlignClass = match ($align) {
        'center' => 'text-center',
        'left' => 'text-left',
        default => 'text-right',
    };
@endphp

<th
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge([
        'class' => 'whitespace-nowrap px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500'
    ]) }}
>
    @if($sortable)
        <div class="flex items-center gap-2 {{ $wrapperClass }}">
            <span class="{{ $textAlignClass }}">
                {{ $label ?? $slot }}
            </span>

            <span class="inline-flex items-center rounded-md  p-0.5 ring-1 ring-slate-200/70">
                <a
                    href="{{ $ascUrl }}"
                    class="flex h-5 w-5 items-center justify-center rounded transition"
                    title="مرتب‌سازی صعودی"
                    aria-label="مرتب‌سازی صعودی {{ $label }}"
                >
                    <svg
                        class="h-3.5 w-3.5 transition {{ $isActive && $currentDirection === 'asc' ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd"
                              d="M10 6.25a.75.75 0 0 1 .53.22l3.5 3.5a.75.75 0 1 1-1.06 1.06L10 8.06l-2.97 2.97a.75.75 0 0 1-1.06-1.06l3.5-3.5A.75.75 0 0 1 10 6.25Z"
                              clip-rule="evenodd"/>
                    </svg>
                </a>

                <a
                    href="{{ $descUrl }}"
                    class="flex h-5 w-5 items-center justify-center rounded transition"
                    title="مرتب‌سازی نزولی"
                    aria-label="مرتب‌سازی نزولی {{ $label }}"
                >
                    <svg
                        class="h-3.5 w-3.5 transition {{ $isActive && $currentDirection === 'desc' ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                        aria-hidden="true"
                    >
                        <path fill-rule="evenodd"
                              d="M10 13.75a.75.75 0 0 1-.53-.22l-3.5-3.5a.75.75 0 0 1 1.06-1.06L10 11.94l2.97-2.97a.75.75 0 1 1 1.06 1.06l-3.5 3.5a.75.75 0 0 1-.53.22Z"
                              clip-rule="evenodd"/>
                    </svg>
                </a>
            </span>
        </div>
    @else
        <div class="{{ $textAlignClass }}">
            {{ $label ?? $slot }}
        </div>
    @endif
</th>
