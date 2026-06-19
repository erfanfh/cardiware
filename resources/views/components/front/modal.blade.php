@props([
    'name',
    'title' => null,
    'description' => null,
    'maxWidth' => 'max-w-lg',
    'closeable' => true,
])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}' || $event.detail?.name === '{{ $name }}') open = false"
    x-on:keydown.escape.window="@if($closeable) open = false @endif"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[999] overflow-y-auto"
    aria-modal="true"
    role="dialog"
>
    <div class="flex min-h-screen items-center justify-center px-4 py-6 sm:py-10">
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition.opacity
            @if($closeable)
                @click="open = false"
            @endif
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm dark:bg-slate-950/75"
        ></div>

        {{-- Modal Box --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative w-full {{ $maxWidth }} overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/10 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/40"
        >
            @if($title || $description || $closeable)
                <div
                    class="flex items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900">
                    <div class="min-w-0">
                        @if($title)
                            <div class="text-base font-black text-slate-900 dark:text-white">
                                {{ $title }}
                            </div>
                        @endif

                        @if($description)
                            <div class="mt-1 text-xs font-bold leading-6 text-slate-500 dark:text-slate-400">
                                {{ $description }}
                            </div>
                        @endif
                    </div>

                    @if($closeable)
                        <button
                            type="button"
                            @click="open = false"
                            class="grid h-9 w-9 shrink-0 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-white hover:text-red-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-rose-300"
                            aria-label="بستن"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M18 6 6 18M6 6l12 12"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <div class="bg-white p-5 dark:bg-slate-900">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/50">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
