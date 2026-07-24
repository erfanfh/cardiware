@php
    $title = $title ?? '';
    $description = $description ?? null;
    $backMethod = $backMethod ?? null;
    $mobile = $mobile ?? null;
@endphp

<div class="space-y-5">
    @if($backMethod)
        <div class="flex justify-end">
            <button
                type="button"
                wire:click="{{ $backMethod }}"
                class="inline-flex cursor-pointer items-center gap-1 text-xs font-medium text-slate-500 transition-colors hover:text-brand-600 dark:text-slate-400 dark:hover:text-brand-400"
            >
                @include('icons.arrow-left-01')

                <span>بازگشت</span>
            </button>
        </div>
    @endif

    <div class="text-center">
        <h2 class="text-lg font-bold tracking-tight text-slate-900 sm:text-xl dark:text-slate-100">
            {{ $title }}
        </h2>

        @if($description)
            <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        @endif

        @if($mobile)
            <div
                class="mt-3 inline-flex max-w-full items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 dark:bg-brand-900/20">
                <svg
                    class="h-4 w-4 shrink-0 text-brand-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"
                    />
                    <path
                        stroke-linecap="round"
                        d="M10 18h4"
                    />
                </svg>

                <span
                    dir="ltr"
                    class="truncate text-xs font-semibold text-slate-700 dark:text-slate-200"
                >
                    {{ $mobile }}
                </span>
            </div>
        @endif
    </div>
</div>
