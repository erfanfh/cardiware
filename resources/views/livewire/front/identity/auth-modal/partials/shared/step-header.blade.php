@php
    $title = $title ?? '';
    $description = $description ?? null;
    $backMethod = $backMethod ?? null;
    $mobile = $mobile ?? null;
@endphp

<div class="mb-5">
    @if($backMethod)
        <div class="mb-3 flex justify-end">
            <button
                type="button"
                wire:click="{{ $backMethod }}"
                wire:loading.attr="disabled"
                class="inline-flex cursor-pointer items-center gap-1 text-[11px] font-extrabold text-slate-500 transition hover:text-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:text-slate-400 dark:hover:text-brand-400"
            >
                @include('icons.arrow-right-01')

                <span>Back</span>
            </button>
        </div>
    @endif

    <div class="text-center">
        <h3 class="text-base font-black text-slate-900 dark:text-slate-100">
            {{ $title }}
        </h3>

        @if($description)
            <p class="mt-1 text-[11px] font-bold leading-5 text-slate-500 dark:text-slate-400">
                {{ $description }}
            </p>
        @endif

        @if($mobile)
            <div
                dir="ltr"
                class="mt-3 inline-flex max-w-full items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 text-xs font-extrabold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300"
            >
                <svg
                    class="h-3.5 w-3.5 shrink-0"
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

                <span class="truncate">
                    {{ $mobile }}
                </span>
            </div>
        @endif
    </div>
</div>
