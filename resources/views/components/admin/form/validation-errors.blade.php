@props([
    'errors' => null,
    'title' => 'خطاهای فرم',
    'description' => 'لطفاً موارد زیر را بررسی و اصلاح کنید.',
    'variant' => 'danger', // danger | warning
    'dismissible' => false,
])

@php
    $bag = $errors ?? session('errors');

    if ($bag instanceof \Illuminate\Support\ViewErrorBag) {
        $bag = $bag->default;
    }

    $messages = $bag?->all() ?? [];

    $styles = match ($variant) {
        'warning' => [
            'wrapper' => 'border-amber-200 bg-amber-50/90',
            'iconWrap' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
            'title' => 'text-amber-900',
            'desc' => 'text-amber-700',
            'item' => 'border-amber-200/80 bg-white/80 text-amber-900',
            'bullet' => 'bg-amber-500',
        ],
        default => [
            'wrapper' => 'border-rose-200 bg-rose-50/90',
            'iconWrap' => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
            'title' => 'text-rose-900',
            'desc' => 'text-rose-700',
            'item' => 'border-rose-200/80 bg-white/80 text-rose-900',
            'bullet' => 'bg-rose-500',
        ],
    };
@endphp

@if(count($messages))
    <div
        x-data="{ open: true }"
        x-show="open"
        x-transition.opacity.duration.200ms
        {{ $attributes->merge([
            'class' => "relative overflow-hidden rounded-2xl border shadow-sm {$styles['wrapper']}",
            'role' => 'alert',
            'aria-live' => 'assertive',
        ]) }}
    >
        <div class="absolute inset-y-0 right-0 w-1.5 bg-current opacity-10"></div>

        <div class="p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $styles['iconWrap'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-black {{ $styles['title'] }}">
                                {{ $title }}
                            </h3>

                            @if($description)
                                <p class="mt-1 text-xs leading-6 {{ $styles['desc'] }}">
                                    {{ $description }}
                                </p>
                            @endif
                        </div>

                        @if($dismissible)
                            <button
                                type="button"
                                x-on:click="open = false"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/70 bg-white/70 text-slate-500 transition hover:bg-white hover:text-slate-700"
                                aria-label="بستن"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-2">
                        @foreach($messages as $message)
                            <div
                                class="flex items-start gap-3 rounded-xl border px-3 py-2.5 text-sm leading-7 {{ $styles['item'] }}">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full {{ $styles['bullet'] }}"></span>
                                <p class="min-w-0">
                                    {{ $message }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
