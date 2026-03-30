<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2"></rect>
              <path d="M3 17l5-5 4 4 3-3 6 6"></path>
              <path d="M14 7h.01"></path>
            </svg>
          </span>
        <h2 class="text-base font-extrabold">تصاویر پیشنمایش</h2>
    </div>

    <div class="mt-4">
        <div class="swiper mwGallery">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img
                        class="h-56 sm:h-64 w-full rounded-2xl border border-slate-200 object-cover"
                        src="https://picsum.photos/seed/mw-g1/1200/800" alt=""></div>
                <div class="swiper-slide"><img
                        class="h-56 sm:h-64 w-full rounded-2xl border border-slate-200 object-cover"
                        src="https://picsum.photos/seed/mw-g2/1200/800" alt=""></div>
                <div class="swiper-slide"><img
                        class="h-56 sm:h-64 w-full rounded-2xl border border-slate-200 object-cover"
                        src="https://picsum.photos/seed/mw-g3/1200/800" alt=""></div>
                <div class="swiper-slide"><img
                        class="h-56 sm:h-64 w-full rounded-2xl border border-slate-200 object-cover"
                        src="https://picsum.photos/seed/mw-g4/1200/800" alt=""></div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <div
                    class="mwGalleryPrev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                    <span class="text-slate-700">→</span>
                </div>
                <div class="mwGalleryPagination flex items-center gap-2"></div>
                <div
                    class="mwGalleryNext h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                    <span class="text-slate-700">←</span>
                </div>
            </div>
        </div>
    </div>
</section>
