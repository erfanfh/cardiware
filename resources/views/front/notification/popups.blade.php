@if($popups->isNotEmpty())
    <div data-mw-popups>
        @foreach($popups as $popup)
            @php($imageOnly = $popup->display_mode === 'image_only')
            <div
                data-mw-popup
                data-popup-key="mw-popup:{{ auth()->id() ?? 'guest' }}:{{ $popup->id }}:{{ $popup->updated_at?->timestamp }}"
                data-popup-delay="{{ $popup->delay_seconds }}"
                data-popup-close-minutes="{{ $popup->close_minutes }}"
                data-popup-hide-days="{{ $popup->hide_days }}"
                class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-slate-950/70 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200 ease-out motion-reduce:transition-none sm:p-8"
                role="presentation"
            >
                <section role="dialog" aria-modal="true" @if($imageOnly) aria-label="{{ $popup->title }}" @else aria-labelledby="popup-title-{{ $popup->id }}" @endif tabindex="-1"
                         class="relative my-auto w-full max-w-xl translate-y-4 scale-95 overflow-hidden rounded-[1.75rem] border border-white/70 bg-white opacity-0 shadow-2xl shadow-slate-950/30 transition-all duration-200 ease-out motion-reduce:transition-none dark:border-slate-700 dark:bg-slate-900">
                    <div class="absolute left-3 top-3 z-10 flex gap-2">
                        <button type="button" data-popup-dismiss="later" aria-label="بستن" title="بستن" class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white/95 text-slate-700 shadow-sm transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 dark:border-slate-600 dark:bg-slate-900/95 dark:text-slate-100 dark:hover:bg-slate-800">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                        </button>
                        <button type="button" data-popup-dismiss="long" aria-label="دیگر نمایش نده" title="دیگر نمایش نده" class="inline-flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white/95 text-slate-700 shadow-sm transition hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 dark:border-slate-600 dark:bg-slate-900/95 dark:text-slate-100 dark:hover:bg-slate-800">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 3 18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A11 11 0 0 1 12 5c6 0 10 7 10 7a18 18 0 0 1-3.2 3.9M6.2 6.2A19 19 0 0 0 2 12s4 7 10 7a11 11 0 0 0 5.8-1.8"/></svg>
                        </button>
                    </div>
                    @if($popup->image?->url)
                        <div @class(['relative overflow-hidden', 'h-48 bg-slate-100 sm:h-60 dark:bg-slate-800' => !$imageOnly])>
                            <img src="{{ $popup->image->url }}" alt="{{ $imageOnly ? $popup->title : '' }}" @class(['w-full', 'h-full object-cover' => !$imageOnly, 'max-h-[calc(100dvh-4rem)] object-contain' => $imageOnly]) loading="lazy">
                            @unless($imageOnly)
                                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/15 to-transparent"></div>
                            @endunless
                        </div>
                    @endif
                    @unless($imageOnly)
                        <div @class(['p-6 sm:p-8', 'pt-16 sm:pt-16' => !$popup->image?->url])>
                            <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                                <span class="h-2 w-2 rounded-full bg-orange-500"></span> اطلاع‌رسانی
                            </div>
                            <h2 id="popup-title-{{ $popup->id }}" class="text-xl font-black leading-8 text-slate-900 sm:text-2xl dark:text-white">{{ $popup->title }}</h2>
                            @if($popup->body)
                                <p class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-600 sm:text-base dark:text-slate-300">{{ $popup->body }}</p>
                            @endif
                        </div>
                    @endunless
                    @if($popup->buttons)
                        <div class="flex flex-wrap gap-3 px-6 pb-6 sm:px-8 sm:pb-8 {{ $imageOnly ? 'pt-4' : '' }}">
                            @foreach($popup->buttons as $button)
                                <a href="{{ $button['url'] }}" class="inline-flex min-h-10 min-w-0 max-w-full items-center justify-center rounded-xl bg-orange-500 px-5 py-2 text-center text-sm font-bold break-words text-white transition hover:bg-orange-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">{{ $button['text'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @endforeach
    </div>
@endif
