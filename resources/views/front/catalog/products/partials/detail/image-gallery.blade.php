<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div>
            <div class="text-base font-extrabold text-slate-900">گالری تصاویر محصول</div>
            <div class="text-xs text-slate-500 mt-1">در هر اسلاید ۶ تصویر نمایش داده می‌شود</div>
        </div>
    </div>

    <div class="swiper productGallerySlider">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <template x-for="image in gallerySet1" :key="'g1-' + image">
                        <button type="button" @click="activeHero = image"
                                class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img :src="image" alt=""
                                 class="h-32 sm:h-36 w-full object-cover transition duration-300 group-hover:scale-105"/>
                        </button>
                    </template>
                </div>
            </div>

            <div class="swiper-slide">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <template x-for="image in gallerySet2" :key="'g2-' + image">
                        <button type="button" @click="activeHero = image"
                                class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <img :src="image" alt=""
                                 class="h-32 sm:h-36 w-full object-cover transition duration-300 group-hover:scale-105"/>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex items-center justify-center gap-2">
        <button
            class="gallery-prev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
            <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                <path d="M14 7l-5 5 5 5" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        <button
            class="gallery-next h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
            <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                <path d="M10 7l5 5-5 5" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</section>
