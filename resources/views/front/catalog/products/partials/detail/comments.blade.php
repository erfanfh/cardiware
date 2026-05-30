<section id="comments"
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm scroll-mt-36">
    <div class="flex items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">💬</span>
            <div>
                <h2 class="text-base font-extrabold">کامنت‌های حرفه‌ای</h2>
                <p class="text-xs text-slate-500 mt-1">نظرات کاربران درباره تجربه نصب و استفاده</p>
            </div>
        </div>

        <button
            class="inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition">
            ثبت دیدگاه
        </button>
    </div>

    <div class="space-y-4">
        <template x-for="comment in comments" :key="comment.name + comment.date">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img :src="comment.avatar" alt=""
                             class="h-12 w-12 rounded-2xl object-cover">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900"
                                 x-text="comment.name"></div>
                            <div class="mt-1 text-xs text-slate-500" x-text="comment.date"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 text-amber-500">
                        <template x-for="star in 5" :key="star">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="m12 17.27 6.18 3.73-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </template>
                    </div>
                </div>

                <p class="mt-4 text-sm text-slate-700 leading-7" x-text="comment.text"></p>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <span
                                            class="text-[11px] font-extrabold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-xl">خریدار تاییدشده</span>
                    <span
                        class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">نصب موفق</span>
                </div>
            </div>
        </template>
    </div>
</section>
