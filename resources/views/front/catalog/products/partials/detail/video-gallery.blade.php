<section id="video-gallery"
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm scroll-mt-36">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">🎬</span>
            <div>
                <h2 class="text-base font-extrabold">تریلر و پیشنمایش ویدیویی</h2>
                <p class="text-xs text-slate-500 mt-1">نمایش ویدیوها به صورت اسلایدری</p>
            </div>
        </div>
    </div>

    <div class="swiper productVideoSlider">
        <div class="swiper-wrapper">
            <template x-for="video in videos" :key="video.title">
                <div class="swiper-slide">
                    <div
                        class="rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-soft transition">
                        <div class="relative">
                            <img :src="video.cover" alt=""
                                 class="h-52 w-full object-cover opacity-90">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>

                            <div class="absolute top-3 right-3">
                                                    <span
                                                        class="text-[11px] font-extrabold text-slate-900 bg-white/90 border border-white px-2 py-1 rounded-xl">ویدیو</span>
                            </div>

                            <div class="absolute top-3 left-3">
                                                    <span
                                                        class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl"
                                                        x-text="video.duration"></span>
                            </div>

                            <button
                                class="absolute inset-0 m-auto h-14 w-14 rounded-full bg-brand-600 hover:bg-brand-700 transition grid place-items-center text-white">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 6v12l10-6-10-6Z"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="p-4">
                            <div class="text-sm font-extrabold text-white"
                                 x-text="video.title"></div>
                            <div class="mt-2 text-xs text-white/60" x-text="video.meta"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-center gap-2">
        <button
            class="video-prev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
            <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button
            class="video-next h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
            <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                <path d="M10 7l5 5-5 5" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</section>
