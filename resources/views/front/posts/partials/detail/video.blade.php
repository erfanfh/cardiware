<x-front.cart>
    <x-front.cart-title
        title="گالری ویدیویی"
        iconName="video-01"
    />

    <div class="mt-4">
        <div class="swiper mwVideos">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div
                        class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-900 shadow-sm dark:border-slate-700 dark:shadow-black/20">
                        <div class="relative">
                            <img src="https://picsum.photos/seed/mw-v1/1200/800"
                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl dark:bg-slate-950/90 dark:text-slate-100">Trailer</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">01:24</span>
                            </div>
                            <button
                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition dark:hover:bg-brand-500">
                                پخش
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="text-sm font-extrabold text-white">نمایش عملکرد نسخه 0.9.3</div>
                            <div class="mt-1 text-xs text-white/60">کیفیت بهتر • لگ کمتر</div>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div
                        class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-900 shadow-sm dark:border-slate-700 dark:shadow-black/20">
                        <div class="relative">
                            <img src="https://picsum.photos/seed/mw-v2/1200/800"
                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl dark:bg-slate-950/90 dark:text-slate-100">Tutorial</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">03:10</span>
                            </div>
                            <button
                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition dark:hover:bg-brand-500">
                                پخش
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="text-sm font-extrabold text-white">آموزش نصب و رفع خطا</div>
                            <div class="mt-1 text-xs text-white/60">قدم به قدم • سریع</div>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div
                        class="rounded-2xl border border-slate-200 overflow-hidden bg-slate-900 shadow-sm dark:border-slate-700 dark:shadow-black/20">
                        <div class="relative">
                            <img src="https://picsum.photos/seed/mw-v3/1200/800"
                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl dark:bg-slate-950/90 dark:text-slate-100">Showcase</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">02:02</span>
                            </div>
                            <button
                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition dark:hover:bg-brand-500">
                                پخش
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <div class="text-sm font-extrabold text-white">مقایسه قبل/بعد</div>
                            <div class="mt-1 text-xs text-white/60">فریم بهتر • تصویر شفاف‌تر</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div
                    class="mwVideosPrev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                    <span class="text-slate-700 dark:text-slate-200">→</span>
                </div>
                <div class="mwVideosPagination flex items-center gap-2"></div>
                <div
                    class="mwVideosNext h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer dark:border-slate-700 dark:bg-slate-950 dark:hover:bg-slate-800">
                    <span class="text-slate-700 dark:text-slate-200">←</span>
                </div>
            </div>
        </div>
    </div>
</x-front.cart>
