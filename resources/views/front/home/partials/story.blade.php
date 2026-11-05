@if(count($stories) > 0)
    <section x-data="storyViewer(@js($stories))" @keydown.window="handleKeydown($event)"
             @visibilitychange.document="visibilityChanged()" aria-label="استوری‌ها">
        <div class="mx-auto max-w-7xl px-4">
            <div class="mw-stories-row flex snap-x items-start gap-4 overflow-x-auto px-1 pb-3 pt-1 sm:gap-6 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($stories as $story)
                    @php
                        // هر استوری رنگ خودش را دارد (حلقه + برچسب)؛ بعد از دیدن، حلقه خاکستری می‌شود.
                        $palette = [
                            ['ring' => 'from-[#f97316] via-[#f43f5e] to-[#a855f7]', 'pill' => 'from-[#f97316] to-[#f43f5e]'],
                            ['ring' => 'from-[#10b981] via-[#06b6d4] to-[#3b82f6]', 'pill' => 'from-[#10b981] to-[#06b6d4]'],
                            ['ring' => 'from-[#ef4444] via-[#f97316] to-[#facc15]', 'pill' => 'from-[#ef4444] to-[#f97316]'],
                            ['ring' => 'from-[#8b5cf6] via-[#d946ef] to-[#ec4899]', 'pill' => 'from-[#8b5cf6] to-[#d946ef]'],
                            ['ring' => 'from-[#0ea5e9] via-[#14b8a6] to-[#84cc16]', 'pill' => 'from-[#0ea5e9] to-[#14b8a6]'],
                        ];
                        $colors = $palette[$loop->index % count($palette)];
                        $badgeText = trim((string) ($story['badge'] ?? ''));
                        $isLive = mb_strpos($badgeText, 'لایو') !== false;
                    @endphp

                    <button type="button" class="group flex w-[5.5rem] shrink-0 snap-start flex-col items-center gap-2.5 text-center sm:w-24"
                            @click="openByIndex({{ $loop->index }})" aria-haspopup="dialog" aria-label="مشاهده استوری {{ $story['title'] }}">
                        <span class="relative block">
                            {{-- حلقه رنگی ضخیم + فاصله سفید؛ استوری دیده‌شده حلقه خاکستری نازک دارد --}}
                            <span class="block h-[5.25rem] w-[5.25rem] rounded-full p-[3px] transition duration-300 group-hover:-translate-y-0.5 group-hover:shadow-[0_10px_24px_-10px_rgba(244,63,94,.55)] sm:h-[5.75rem] sm:w-[5.75rem]"
                                  :class="isSeen(@js($story['id'])) ? 'bg-slate-300 dark:bg-slate-700 p-[2px]' : 'bg-gradient-to-tr {{ $colors['ring'] }}'">
                                <span class="block h-full w-full rounded-full bg-white p-[2px] dark:bg-slate-950">
                                    {{-- کمی بزرگ‌نمایی تا لوگوهایی که حاشیه سفید دارند دایره را پر کنند --}}
                                    <span class="block h-full w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <img src="{{ $story['thumb'] }}" alt=""
                                         class="h-full w-full scale-[1.18] object-cover transition duration-300 group-hover:scale-[1.24]"
                                         loading="lazy" width="88" height="88">
                                    </span>
                                </span>
                            </span>

                            @if($badgeText !== '')
                                <span class="absolute -bottom-2 left-1/2 z-10 -translate-x-1/2 whitespace-nowrap rounded-full border-2 border-white bg-gradient-to-l px-2.5 py-[3px] text-[10px] font-black leading-none text-white shadow-md dark:border-slate-950 {{ $isLive ? 'from-red-600 to-red-500' : $colors['pill'] }}">
                                    @if($isLive)
                                        <span class="ml-1 inline-block h-1.5 w-1.5 animate-pulse rounded-full bg-white align-middle"></span>
                                    @endif
                                    {{ $badgeText }}
                                </span>
                            @endif
                        </span>

                        <span class="line-clamp-2 w-full text-[12px] font-extrabold leading-5 text-slate-700 transition group-hover:text-brand-600 dark:text-slate-300 dark:group-hover:text-brand-300">{{ $story['title'] }}</span>
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
