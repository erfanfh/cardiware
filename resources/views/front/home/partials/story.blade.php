@if(count($stories) > 0)
    <section x-data="storyViewer(@js($stories))" @keydown.window="handleKeydown($event)"
             @visibilitychange.document="visibilityChanged()" aria-label="استوری‌ها">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex items-center gap-6 overflow-x-auto pb-2 sm:gap-9 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($stories as $story)
                    <button type="button" class="group flex shrink-0 flex-col items-center gap-2 text-center"
                            @click="openByIndex({{ $loop->index }})" aria-haspopup="dialog" aria-label="مشاهده استوری {{ $story['title'] }}">
                        <span class="h-16 w-16 rounded-full bg-gradient-to-tr from-brand-400 to-brand-600 p-[2px]">
                            <span class="grid h-full w-full place-items-center rounded-full bg-white dark:bg-slate-900">
                                <img src="{{ $story['thumb'] }}" alt="" class="h-14 w-14 rounded-full object-cover" loading="lazy" width="56" height="56">
                            </span>
                        </span>
                        <span class="max-w-24 truncate text-xs font-semibold text-slate-600 transition group-hover:text-slate-800 dark:text-slate-400 dark:group-hover:text-slate-200">{{ $story['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <template x-teleport="body">
            <div x-show="isOpen" x-transition.opacity.duration.150ms style="display:none"
                 class="fixed inset-0 z-[70] flex items-center justify-center p-3">
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="close()" aria-hidden="true"></div>
                <div x-ref="dialog" role="dialog" aria-modal="true" :aria-label="current.title || 'نمایش استوری'" tabindex="-1"
                     class="relative h-[min(760px,88dvh)] w-[min(420px,94vw)] overflow-hidden rounded-3xl border border-white/10 bg-slate-900 shadow-2xl">
                    <img :src="isOpen ? current.image : null" :alt="current.title || ''" x-on:error="imageError = true" @load="imageError = false"
                         class="absolute inset-0 h-full w-full select-none object-contain" draggable="false">
                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/55"></div>
                    <p x-show="imageError" class="absolute inset-0 grid place-items-center p-8 text-center text-sm text-white">بارگذاری تصویر ممکن نشد. از دکمهٔ بعدی استفاده کنید.</p>

                    <div class="absolute inset-x-0 top-0 z-30 p-3">
                        <div class="flex items-center gap-1.5" aria-hidden="true">
                            <template x-for="(item, i) in stories" :key="item.id">
                                <div class="h-1 flex-1 overflow-hidden rounded-full bg-white/25">
                                    <div class="h-full rounded-full bg-white" :style="progressStyle(i)"></div>
                                </div>
                            </template>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <img :src="isOpen ? current.thumb : null" alt="" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                <div class="min-w-0 text-white">
                                    <div class="truncate text-sm font-extrabold" x-text="current.title"></div>
                                    <div class="truncate text-xs text-white/70" x-text="current.subtitle"></div>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <button type="button" @click="paused ? resume() : pause()" :aria-label="paused ? 'ادامه نمایش' : 'توقف نمایش'" :aria-pressed="paused"
                                        class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15 text-sm font-bold text-white hover:bg-white/25">
                                    <span x-text="paused ? '▶' : 'Ⅱ'" aria-hidden="true"></span>
                                </button>
                                <button x-ref="closeButton" type="button" @click="close()" aria-label="بستن استوری"
                                        class="grid h-10 w-10 place-items-center rounded-2xl bg-white/15 text-xl text-white hover:bg-white/25">×</button>
                            </div>
                        </div>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 z-30 space-y-3 p-4">
                        <div x-show="current.caption || current.href" class="rounded-2xl bg-black/35 px-4 py-3 text-white backdrop-blur">
                            <p x-show="current.caption" class="max-h-36 overflow-y-auto whitespace-pre-line break-words text-sm leading-7" x-text="current.caption"></p>
                            <template x-if="current.href">
                                <a :href="current.href" class="mt-2 inline-flex rounded-xl bg-brand-500 px-4 py-2 text-xs font-extrabold text-white hover:bg-brand-600" x-text="current.linkLabel"></a>
                            </template>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-white">
                            <button type="button" @click="prev()" :disabled="index === 0" aria-label="استوری قبلی"
                                    class="rounded-xl bg-white/15 px-4 py-2 text-xs font-bold hover:bg-white/25 disabled:opacity-30">قبلی</button>
                            <span class="text-xs text-white/80" x-text="`${index + 1} / ${stories.length}`" aria-live="polite"></span>
                            <button type="button" @click="next()" aria-label="استوری بعدی"
                                    class="rounded-xl bg-white/15 px-4 py-2 text-xs font-bold hover:bg-white/25">بعدی</button>
                        </div>
                    </div>

                    <div class="absolute inset-0 z-20 touch-none" aria-hidden="true"
                         @pointerdown.prevent="hold()" @pointerup="release()" @pointerleave="release()" @pointercancel="release()" @contextmenu.prevent></div>
                </div>
            </div>
        </template>
    </section>
@endif
