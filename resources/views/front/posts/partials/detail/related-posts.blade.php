<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
              </svg>
            </span>
            <h2 class="text-base font-extrabold">نوشته‌های مرتبط</h2>
        </div>
    </div>

    <!-- new layout: split card + thumbnail strip style -->
    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Featured related -->
        <a href="#"
           class="rounded-2xl border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-soft transition group">
            <div class="relative">
                <img src="https://picsum.photos/seed/mw-rel-feature/1200/800" alt=""
                     class="h-44 sm:h-52 w-full object-cover opacity-80 group-hover:scale-[1.02] transition duration-500">
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-transparent"></div>
                <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl">پیشنهادی</span>
                    <span
                        class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۶ دقیقه</span>
                </div>
                <div class="absolute bottom-3 right-3 left-3">
                    <div class="text-white font-extrabold text-base leading-7 line-clamp-2">
                        رفع باگ‌های گزارش‌شده و بهبود سازگاری با مودها
                    </div>
                    <div class="mt-2 text-xs text-white/70">۱.۶k بازدید • آپدیت پچ</div>
                </div>
            </div>
        </a>

        <!-- List style -->
        <div class="space-y-3">
            <a href="#"
               class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                <img src="https://picsum.photos/seed/mw-rel-a/140/140" alt=""
                     class="h-16 w-16 rounded-2xl object-cover border border-slate-200">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">آموزش</span>
                        <span class="text-xs text-slate-500">۳.۱k بازدید</span>
                    </div>
                    <div class="mt-2 text-sm font-extrabold leading-6 truncate">تنظیمات پیشنهادی برای
                        بهترین
                        FPS
                    </div>
                    <div class="mt-1 text-xs text-slate-500 truncate">نکات ساده ولی کاربردی برای تجربه
                        روان‌تر…
                    </div>
                </div>
            </a>

            <a href="#"
               class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                <img src="https://picsum.photos/seed/mw-rel-b/140/140" alt=""
                     class="h-16 w-16 rounded-2xl object-cover border border-slate-200">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                                    <span
                                        class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">اطلاعیه</span>
                        <span class="text-xs text-slate-500">۱.۹k بازدید</span>
                    </div>
                    <div class="mt-2 text-sm font-extrabold leading-6 truncate">قوانین ارسال گزارش باگ
                        در
                        مودینگ‌وی
                    </div>
                    <div class="mt-1 text-xs text-slate-500 truncate">با این قالب، بررسی باگ‌ها سریع‌تر
                        انجام می‌شود…
                    </div>
                </div>
            </a>
        </div>
    </div>
</section>
