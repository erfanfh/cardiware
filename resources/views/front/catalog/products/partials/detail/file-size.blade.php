<section id="file-size"
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm scroll-mt-36">
    <div class="flex items-center gap-3">
                            <span
                                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">📦</span>
        <div>
            <h2 class="text-base font-extrabold">حجم فایل‌ها</h2>
            <p class="text-xs text-slate-500 mt-1">جزئیات فایل‌های اصلی و مکمل</p>
        </div>
    </div>

    <div class="mt-5 space-y-3">
        <template x-for="file in files" :key="file.name">
            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="text-sm font-extrabold text-slate-900" x-text="file.name"></div>
                    <div class="mt-1 text-xs text-slate-500" x-text="file.desc"></div>
                </div>
                <div class="shrink-0 text-sm font-black text-brand-700" x-text="file.size"></div>
            </div>
        </template>
    </div>
</section>
