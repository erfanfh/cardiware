<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <div>
            <div class="text-sm font-extrabold text-slate-900">درصد پیشرفت آپدیت محصول</div>
            <div class="text-xs text-slate-500 mt-1">پیشرفت تا زمان انتشار آپدیت بعدی</div>
        </div>

        <span
            class="text-[11px] font-extrabold px-2 py-1 rounded-xl border"
            :class="updateProgress >= 100
                                    ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                                    : 'text-brand-800 bg-brand-50 border-brand-100'"
            x-text="updateProgress >= 100 ? 'منتشر شد' : 'درحال آماده‌سازی'"
        ></span>
    </div>

    <div class="mt-5">
        <div class="flex items-center justify-between text-xs text-slate-500">
            <span>شروع توسعه</span>
            <span>تاریخ انتشار: ۱۴۰۵/۰۱/۱۰</span>
        </div>

        <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
            <div
                class="h-full rounded-full transition-all duration-500"
                :class="updateProgress >= 100 ? 'bg-emerald-500' : 'bg-brand-500'"
                :style="`width:${updateProgress}%`"
            ></div>
        </div>

        <div class="mt-3 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                                    <span class="font-extrabold text-slate-800"
                                          x-text="updateProgress + '%'"></span>
                تکمیل شده
            </div>
            <div class="text-xs font-extrabold"
                 :class="updateProgress >= 100 ? 'text-emerald-700' : 'text-brand-700'"
                 x-text="updateProgress >= 100 ? 'آپدیت منتشر شد' : 'آپدیت در مسیر انتشار'"></div>
        </div>
    </div>
</section>
