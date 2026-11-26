<div
    x-data="lightboxSystem()"
    x-cloak
    @lightbox:open.window="open($event.detail.items, $event.detail.index ?? 0)"
    @keydown.window.escape="if (isOpen) close()"
    @keydown.window.left="if (isOpen) prev()"
    @keydown.window.right="if (isOpen) next()"
    class="fixed inset-0 z-[2000] flex items-center justify-center p-3 sm:p-6"
    :class="isOpen ? 'pointer-events-auto' : 'pointer-events-none'"
>
    {{-- backdrop --}}
    <div
        @click="close()"
        class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm transition duration-300"
        :class="isOpen ? 'opacity-100' : 'opacity-0'"
    ></div>

    {{-- panel --}}
    <div
        class="relative z-10 w-full max-w-6xl transition duration-300 ease-out"
        :class="isOpen
            ? 'opacity-100 scale-100 translate-y-0'
            : 'opacity-0 scale-95 translate-y-2'"
    >
        <div class="rounded-[1.75rem] border border-white/10 bg-white/5 p-2 sm:p-3 backdrop-blur-xl">
            <div class="relative overflow-hidden rounded-[1.4rem] bg-slate-950">

                {{-- close --}}
                <button
                    type="button"
                    @click="close()"
                    class="absolute cursor-pointer left-3 top-3 z-20 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    :class="isOpen ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
                    aria-label="Close"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>

                {{-- image --}}
                <img
                    x-show="current.src"
                    :src="current.src"
                    :alt="current.title || 'image'"
                    class="h-[60vh] sm:h-[68vh] w-full object-contain bg-slate-950 select-none"
                    draggable="false"
                >

                {{-- prev --}}
                <button
                    type="button"
                    @click="prev()"
                    x-show="items.length > 1"
                    class="absolute cursor-pointer [&_svg]:w-6 [&_svg]:h-6 right-3 top-1/2 z-20 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    aria-label="Previous image"
                >
                    @include('icons.arrow-left-01')
                </button>

                {{-- next --}}
                <button
                    type="button"
                    @click="next()"
                    x-show="items.length > 1"
                    class="absolute cursor-pointer [&_svg]:w-6 [&_svg]:h-6 left-3 top-1/2 z-20 inline-flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-white backdrop-blur transition hover:bg-white/20"
                    aria-label="Next image"
                >
                    @include('icons.arrow-right-01')
                </button>

                {{-- bottom overlay --}}
                <div
                    class="absolute inset-x-0 bottom-0 z-10 bg-gradient-to-t from-black/70 via-black/30 to-transparent p-4 sm:p-5 transition duration-300"
                    :class="isOpen ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
                >
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

            {{-- thumbnails --}}
            <div
                x-show="items.length > 1"
                class="lightbox-thumbs-scrollbar mt-3 overflow-x-auto pb-1"
                x-ref="thumbs"
            >
                <div class="flex items-center justify-center gap-2 min-w-max">
                    <template x-for="(item, thumbIndex) in items" :key="`${item.src}-${thumbIndex}`">
                        <button
                            type="button"
                            @click="goTo(thumbIndex)"
                            class="group relative shrink-0 overflow-hidden rounded-2xl border transition"
                            :class="thumbIndex === index
                                ? 'border-white/40 ring-2 ring-white/20 opacity-100'
                                : 'border-white/10 opacity-70 hover:opacity-100'"
                            :aria-label="`View image ${thumbIndex + 1}`"
                        >
                            <img
                                :src="item.src"
                                :alt="item.title || `thumb-${thumbIndex + 1}`"
                                class="w-12 h-8 sm:w-24 sm:h-16 cursor-pointer object-cover bg-slate-900"
                                draggable="false"
                            >

                            <div
                                class="pointer-events-none absolute inset-0 transition"
                                :class="thumbIndex === index ? 'bg-transparent' : 'bg-slate-950/25 group-hover:bg-slate-950/10'"
                            ></div>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
