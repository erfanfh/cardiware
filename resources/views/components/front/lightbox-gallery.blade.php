<div
    x-data="lightboxSystem()"
    x-show="isOpen"
    x-cloak
    @lightbox:open.window="open($event.detail.items, $event.detail.index ?? 0)"
    @keydown.window.escape="if (isOpen) close()"
    @keydown.window.right="if (isOpen) prev()"
    @keydown.window.left="if (isOpen) next()"
    x-transition.opacity
    class="fixed inset-0 z-[2000] flex items-center justify-center p-3 sm:p-6"
>
    <div
        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm"
        @click="close()"
    ></div>

    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="relative z-10 w-full max-w-6xl"
    >
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-2 sm:p-3 backdrop-blur-xl">
            <div class="relative overflow-hidden rounded-[1.4rem] bg-slate-950">

                {{-- close --}}
                <button
                    type="button"
                    @click="close()"
                    class="absolute left-3 top-3 z-20 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    aria-label="بستن"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>

                {{-- image --}}
                <template x-if="current.src">
                    <img
                        :src="current.src"
                        :alt="current.title || 'image'"
                        class="h-[68vh] sm:h-[75vh] w-full object-contain bg-slate-950 select-none"
                        draggable="false"
                    >
                </template>

                {{-- prev --}}
                <button
                    type="button"
                    @click="prev()"
                    x-show="items.length > 1"
                    class="absolute right-3 top-1/2 z-20 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    aria-label="تصویر قبلی"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>

                {{-- next --}}
                <button
                    type="button"
                    @click="next()"
                    x-show="items.length > 1"
                    class="absolute left-3 top-1/2 z-20 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    aria-label="تصویر بعدی"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/>
                    </svg>
                </button>

                {{-- bottom overlay --}}
                <div
                    class="absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black/70 via-black/30 to-transparent p-4 sm:p-5">
                    <div class="flex items-end justify-between gap-4">
                        <div class="min-w-0">
                            <p
                                x-text="current.title || ''"
                                class="truncate text-sm sm:text-base font-bold text-white"
                            ></p>
                        </div>

                        <div
                            x-show="items.length > 1"
                            class="shrink-0 rounded-xl border border-white/10 bg-white/10 px-3 py-1.5 text-xs sm:text-sm font-bold text-white backdrop-blur"
                        >
                            <span x-text="index + 1"></span>
                            <span>/</span>
                            <span x-text="items.length"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 flex justify-center">
            <div
                class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 text-xs sm:text-sm text-white/90 backdrop-blur">
                برای بستن کلید ESC را بزنید
            </div>
        </div>
    </div>
</div>
