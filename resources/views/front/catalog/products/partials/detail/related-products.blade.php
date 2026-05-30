<section id="related"
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm scroll-mt-36">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">🧩</span>
            <div>
                <h2 class="text-base font-extrabold">محصولات مشابه</h2>
                <p class="text-xs text-slate-500 mt-1">موارد مرتبط برای همین سبک محصول</p>
            </div>
        </div>

        <a href="#"
           class="text-sm font-semibold text-brand-700 hover:text-brand-800 inline-flex items-center gap-2">
            مشاهده همه
            <span class="font-black">←</span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="item in relatedProducts" :key="item.title">
            <article
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-soft transition">
                <div class="relative overflow-hidden rounded-2xl">
                    <img :src="item.cover" alt="" class="h-44 w-full object-cover">
                    <div class="absolute top-3 right-3">
                                            <span
                                                class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl"
                                                x-text="item.badge"></span>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="text-sm font-extrabold text-slate-900 leading-7"
                        x-text="item.title"></h3>
                    <div class="mt-2 text-xs text-slate-500" x-text="item.meta"></div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs text-slate-500">قیمت</div>
                            <div class="text-sm font-black text-brand-700"
                                 x-text="item.price"></div>
                        </div>
                        <a href="#"
                           class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">
                            مشاهده
                        </a>
                    </div>
                </div>
            </article>
        </template>
    </div>
</section>
