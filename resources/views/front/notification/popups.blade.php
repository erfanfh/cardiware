@if($popups->isNotEmpty())
    <div data-mw-popups>
        @foreach($popups as $popup)
            <div
                data-mw-popup
                data-popup-key="mw-popup:{{ auth()->id() ?? 'guest' }}:{{ $popup->id }}:{{ $popup->updated_at?->timestamp }}"
                data-popup-delay="{{ $popup->delay_seconds }}"
                data-popup-close-minutes="{{ $popup->close_minutes }}"
                data-popup-hide-days="{{ $popup->hide_days }}"
                class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto bg-slate-950/70 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200 ease-out motion-reduce:transition-none sm:p-8"
                role="presentation"
            >
                <section role="dialog" aria-modal="true" aria-labelledby="popup-title-{{ $popup->id }}" tabindex="-1"
                         class="relative my-auto w-full max-w-xl translate-y-4 scale-95 overflow-hidden rounded-[1.75rem] border border-white/70 bg-white opacity-0 shadow-2xl shadow-slate-950/30 transition-all duration-200 ease-out motion-reduce:transition-none dark:border-slate-700 dark:bg-slate-900">
                    @if($popup->image?->url)
                        <div class="relative h-48 overflow-hidden bg-slate-100 sm:h-60 dark:bg-slate-800">
                            <img src="{{ $popup->image->url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/15 to-transparent"></div>
                        </div>
                    @endif
                    <div class="p-6 sm:p-8">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-500/10 dark:text-orange-300">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span> اطلاع‌رسانی
                        </div>
                        <h2 id="popup-title-{{ $popup->id }}" class="text-xl font-black leading-8 text-slate-900 sm:text-2xl dark:text-white">{{ $popup->title }}</h2>
                        @if($popup->body)
                            <p class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-600 sm:text-base dark:text-slate-300">{{ $popup->body }}</p>
                        @endif
                        <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row">
                            <button type="button" data-popup-dismiss="later" class="inline-flex min-h-11 flex-1 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">بستن</button>
                            <button type="button" data-popup-dismiss="long" class="inline-flex min-h-11 flex-1 cursor-pointer items-center justify-center rounded-2xl bg-orange-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">دیگر نشان نده</button>
                        </div>
                    </div>
                </section>
            </div>
        @endforeach
    </div>
@endif
