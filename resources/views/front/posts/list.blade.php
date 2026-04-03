@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">

        <!-- Header -->
        <section class="mb-6">
            <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <div>
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                        آرشیو مطالب
                    </div>
                    <h1 class="mt-3 text-2xl sm:text-3xl font-black leading-tight">
                        نوشته‌ها، آپدیت‌ها و خبرهای مودینگ‌وی
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-slate-600">
                        آخرین پست‌ها درباره پچ‌ها، پیشرفت پروژه‌ها، آموزش‌ها و اطلاعیه‌ها را اینجا ببینید.
                    </p>
                </div>
            </div>
        </section>

        <!-- Layout -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- MAIN -->
            <div class="lg:col-span-8">
                <!-- Search + Filters -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4">

                        <!-- Search -->
                        <div class="relative">
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M21 21l-4.3-4.3"></path>
              </svg>
            </span>

                            <input
                                type="text"
                                placeholder="جستجو در نوشته‌ها… (مثلاً: پچ، آموزش، مودینگ)"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 pr-10 pl-12 py-3 text-sm outline-none
                     focus:bg-white focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"/>

                            <button
                                class="absolute left-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center"
                                aria-label="فیلتر سریع">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" class="text-slate-700">
                                    <path d="M3 5h18"></path>
                                    <path d="M7 12h10"></path>
                                    <path d="M10 19h4"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Filter row -->
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex flex-wrap gap-2">
                                <button
                                    class="h-10 px-4 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold text-sm">
                                    همه
                                </button>
                                <button
                                    class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold text-sm hover:bg-slate-50 transition">
                                    اطلاعیه‌ها
                                </button>
                                <button
                                    class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold text-sm hover:bg-slate-50 transition">
                                    آموزش‌ها
                                </button>
                                <button
                                    class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold text-sm hover:bg-slate-50 transition">
                                    آپدیت پچ
                                </button>
                                <button
                                    class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-slate-800 font-extrabold text-sm hover:bg-slate-50 transition">
                                    پشت‌صحنه
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                                <select class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
                             focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                                    <option>جدیدترین</option>
                                    <option>پربازدیدترین</option>
                                    <option>محبوب‌ترین</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <div class="inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded bg-brand-600"></span>
                                <span>نمایش 1 تا 9 از 128 نوشته</span>
                            </div>
                            <a href="#" class="font-bold text-brand-700 hover:text-brand-800 transition">پاک کردن
                                فیلترها</a>
                        </div>

                    </div>
                </div>

                <!-- Posts Grid (9 cards) -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- 1 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post1/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">اطلاعیه</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۴ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">برنامه انتشار نسخه جدید پچ و
                                    زمان‌بندی آپدیت‌ها</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                مسیر توسعه، تغییرات نسخه بعدی و نکات مهم نصب و سازگاری…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۲۱ بهمن ۱۴۰۴</span>
                                    <span>•</span><span>۱.۲k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 2 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post2/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">آموزش</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۷ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">راهنمای نصب و رفع خطاهای رایج در
                                    نسخه‌های مختلف بازی</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                چک‌لیست سریع برای کرش‌ها، ناسازگاری‌ها و مشکل‌های بعد از نصب…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۱۵ بهمن ۱۴۰۴</span>
                                    <span>•</span><span>۸۹۰ بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 3 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post3/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">آپدیت پچ</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۳ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">لیست تغییرات نسخه 0.9.3 و بهبودهای
                                    عملکردی</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                این نسخه روی پایداری، سرعت لود و سازگاری با برخی سیستم‌ها تمرکز دارد…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۰۷ بهمن ۱۴۰۴</span>
                                    <span>•</span><span>۲.۴k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 4 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post4/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">پشت‌صحنه</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۵ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">پشت‌صحنه: چطور سیستم نصب خودکار را
                                    پایدار کردیم؟</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                از لاگ‌گیری تا بهینه‌سازی فایل‌ها و کاهش خطاها—داستان این بهبود…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۰۱ بهمن ۱۴۰۴</span>
                                    <span>•</span><span>۶۱۰ بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 5 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post5/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">اطلاعیه</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۲ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">قوانین ارسال گزارش باگ (Bug Report)
                                    در مودینگ‌وی</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                برای بررسی سریع‌تر، لطفاً این قالب گزارش و اطلاعات سیستم را رعایت کنید…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۲۸ دی ۱۴۰۴</span>
                                    <span>•</span><span>۱.۹k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 6 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post6/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">آموزش</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۹ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">تنظیمات پیشنهادی برای بهترین FPS بعد
                                    از نصب پچ</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                چند تنظیم ساده اما مهم برای تجربه روان‌تر و بدون لگ در بازی…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۲۰ دی ۱۴۰۴</span>
                                    <span>•</span><span>۳.۱k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 7 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post7/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">آپدیت پچ</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۶ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">رفع باگ‌های گزارش‌شده و بهبود
                                    سازگاری با مودها</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                گزارش کامل از باگ‌فیکس‌ها، اصلاح مسیرها و بهبود هماهنگی با برخی مودها…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۱۴ دی ۱۴۰۴</span>
                                    <span>•</span><span>۱.۶k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 8 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post8/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">پشت‌صحنه</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۴ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">چرا بعضی سیستم‌ها ارور فایل‌های ناقص
                                    می‌گیرند؟</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                بررسی دلایل رایج: آنتی‌ویروس، مسیر نصب، مجوزها و راه‌حل‌های پیشنهادی…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۱۰ دی ۱۴۰۴</span>
                                    <span>•</span><span>۷۸۰ بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>

                    <!-- 9 -->
                    <article
                        class="rounded-xl2 border border-slate-200 bg-white shadow-sm hover:shadow-lg transition overflow-hidden group">
                        <a href="#" class="block relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent pointer-events-none"></div>
                            <img src="https://picsum.photos/seed/mw-post9/800/600" alt=""
                                 class="h-40 w-full object-cover group-hover:scale-[1.02] transition duration-500"/>
                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">آموزش</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">۸ دقیقه</span>
                            </div>
                        </a>
                        <div class="p-4">
                            <h3 class="text-sm font-extrabold leading-6">
                                <a href="#" class="hover:text-brand-700 transition">راهنمای بکاپ‌گیری از تنظیمات قبل از
                                    نصب نسخه جدید</a>
                            </h3>
                            <p class="mt-2 text-xs text-slate-500 leading-6 line-clamp-2">
                                قبل از آپدیت، چطور تنظیمات و فایل‌های مهم را ذخیره کنیم تا در صورت نیاز برگردانیم…
                            </p>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-2"><span
                                            class="h-2 w-2 rounded bg-slate-300"></span>۰۶ دی ۱۴۰۴</span>
                                    <span>•</span><span>۱.۱k بازدید</span>
                                </div>
                                <a href="#"
                                   class="inline-flex items-center gap-2 text-sm font-extrabold text-brand-700 hover:text-brand-800 transition">
                                    ادامه <span class="font-black">←</span>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Pagination -->
                <div class="mt-6 rounded-xl2 border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs text-slate-500">
                            صفحه <span class="font-extrabold text-slate-800">1</span> از <span
                                class="font-extrabold text-slate-800">15</span>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-2">
                            <button
                                class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                                قبلی
                            </button>

                            <div class="flex items-center gap-2">
                                <button
                                    class="h-10 w-10 rounded-2xl border border-brand-100 bg-brand-50 text-sm font-extrabold text-slate-900">
                                    1
                                </button>
                                <button
                                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                                    2
                                </button>
                                <button
                                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                                    3
                                </button>
                                <span class="px-2 text-slate-400">…</span>
                                <button
                                    class="h-10 w-10 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                                    15
                                </button>
                            </div>

                            <button
                                class="h-10 px-4 rounded-2xl bg-brand-500 text-white text-sm font-extrabold shadow-lg hover:bg-brand-600 active:bg-brand-700 transition">
                                بعدی
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Tools (2 columns) -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14.7 6.3a1 1 0 0 0-1.4 0l-7 7a1 1 0 0 0 0 1.4l2 2a1 1 0 0 0 1.4 0l7-7a1 1 0 0 0 0-1.4z"></path>
              <path d="M19 5l-4 4"></path>
              <path d="M22 7l-2-2"></path>
            </svg>
          </span>
                        <h2 class="text-base font-extrabold">ابزارهای کاربردی</h2>
                    </div>

                    <p class="mt-2 text-xs text-slate-500 leading-6">
                        ابزارهای پیشنهادی برای ادیت، تست و مدیریت فایل‌ها.
                    </p>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <!-- SVG ICON PLACEHOLDER -->
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">Photoshop</div>
                                    <div class="text-xs text-slate-500 truncate">Image</div>
                                </div>
                            </div>
                        </a>

                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">Premiere</div>
                                    <div class="text-xs text-slate-500 truncate">Video</div>
                                </div>
                            </div>
                        </a>

                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">After Effects</div>
                                    <div class="text-xs text-slate-500 truncate">Motion</div>
                                </div>
                            </div>
                        </a>

                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">Blender</div>
                                    <div class="text-xs text-slate-500 truncate">3D</div>
                                </div>
                            </div>
                        </a>

                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">Notepad++</div>
                                    <div class="text-xs text-slate-500 truncate">Text</div>
                                </div>
                            </div>
                        </a>

                        <a href="#"
                           class="rounded-2xl border border-slate-200 bg-white p-3 hover:bg-slate-50 hover:shadow-sm transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-11 w-11 rounded-2xl bg-slate-50 border border-slate-200 grid place-items-center">
                                    <span class="text-slate-500 text-xs font-extrabold">SVG</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">7-Zip</div>
                                    <div class="text-xs text-slate-500 truncate">Archive</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Categories -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h7v7H4z"></path>
              <path d="M13 4h7v7h-7z"></path>
              <path d="M4 13h7v7H4z"></path>
              <path d="M13 13h7v7h-7z"></path>
            </svg>
          </span>
                        <h2 class="text-base font-extrabold">دسته‌بندی مقالات</h2>
                    </div>

                    <ul class="mt-4 space-y-3">
                        <li>
                            <a href="#"
                               class="flex items-center justify-between rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                <span class="font-semibold text-slate-800">اطلاعیه‌ها</span>
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">32</span>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               class="flex items-center justify-between rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                <span class="font-semibold text-slate-800">آموزش‌ها</span>
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">41</span>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               class="flex items-center justify-between rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                <span class="font-semibold text-slate-800">آپدیت پچ</span>
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">18</span>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               class="flex items-center justify-between rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                <span class="font-semibold text-slate-800">پشت‌صحنه</span>
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl">9</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Most viewed with tabs -->
                <div
                    x-data="{
          range: 'week',
          items: {
            week: [
              { img: 'https://picsum.photos/seed/mw-topw1/120/120', title: 'چک‌لیست نصب پچ روی نسخه‌های مختلف', meta: '۱.۴k بازدید • ۹ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topw2/120/120', title: 'رفع خطاهای رایج بعد از نصب', meta: '۱.۱k بازدید • ۷ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topw3/120/120', title: 'تنظیمات پیشنهادی برای FPS بهتر', meta: '۹۸۰ بازدید • ۹ دقیقه' },
            ],
            month: [
              { img: 'https://picsum.photos/seed/mw-topm1/120/120', title: 'چک‌لیست نصب پچ روی نسخه‌های مختلف', meta: '۳.۹k بازدید • ۹ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topm2/120/120', title: 'لیست تغییرات نسخه 0.9.3', meta: '۲.۴k بازدید • ۳ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topm3/120/120', title: 'بکاپ‌گیری از تنظیمات قبل از آپدیت', meta: '۱.۷k بازدید • ۸ دقیقه' },
            ],
            year: [
              { img: 'https://picsum.photos/seed/mw-topy1/120/120', title: 'راهنمای نصب کامل + رفع کرش‌ها', meta: '۱۸.۲k بازدید • ۱۰ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topy2/120/120', title: 'بهترین تنظیمات گرافیکی برای سیستم ضعیف', meta: '۱۴.۶k بازدید • ۹ دقیقه' },
              { img: 'https://picsum.photos/seed/mw-topy3/120/120', title: 'لیست تغییرات نسخه‌های مهم سال', meta: '۱۱.۴k بازدید • ۶ دقیقه' },
            ]
          }
        }"
                    class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </span>
                            <h2 class="text-base font-extrabold">مطالب پربازدید</h2>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                @click="range='week'"
                                :class="range==='week'
                ? 'h-9 px-3 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold text-xs'
                : 'h-9 px-3 rounded-2xl border border-slate-200 bg-white text-slate-700 font-extrabold text-xs hover:bg-slate-50 transition'"
                            >هفته
                            </button>

                            <button
                                @click="range='month'"
                                :class="range==='month'
                ? 'h-9 px-3 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold text-xs'
                : 'h-9 px-3 rounded-2xl border border-slate-200 bg-white text-slate-700 font-extrabold text-xs hover:bg-slate-50 transition'"
                            >ماه
                            </button>

                            <button
                                @click="range='year'"
                                :class="range==='year'
                ? 'h-9 px-3 rounded-2xl border border-brand-100 bg-brand-50 text-slate-900 font-extrabold text-xs'
                : 'h-9 px-3 rounded-2xl border border-slate-200 bg-white text-slate-700 font-extrabold text-xs hover:bg-slate-50 transition'"
                            >سال
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <template x-for="(it, idx) in items[range]" :key="range + '-' + idx">
                            <a href="#"
                               class="flex gap-3 rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                <img :src="it.img" alt="" class="h-14 w-14 rounded-2xl object-cover"/>
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold leading-6 truncate" x-text="it.title"></div>
                                    <div class="mt-1 text-xs text-slate-500" x-text="it.meta"></div>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- ModdingWay projects -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 2l8 4v6c0 5-3.5 9.5-8 10-4.5-.5-8-5-8-10V6l8-4z"></path>
              <path d="M8 12h8"></path>
            </svg>
          </span>
                        <div>
                            <h2 class="text-base font-extrabold">پروژه‌های مودینگ‌وی</h2>
                            <p class="text-xs text-slate-500 mt-1">درصد پیشرفت و پروژه‌های جدید</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">پچ فارسی‌ساز نسخه جدید</div>
                                    <div class="mt-1 text-xs text-slate-500 truncate">بهبود فونت، زیرنویس و منوها</div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">62%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-brand-500 w-[62%]"></div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">پکیج بهینه‌سازی گرافیک</div>
                                    <div class="mt-1 text-xs text-slate-500 truncate">FPS بهتر برای سیستم‌های متوسط
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">38%</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-brand-500 w-[38%]"></div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold truncate">سیستم نصب جدید (Installer)</div>
                                    <div class="mt-1 text-xs text-slate-500 truncate">نصب ساده + تشخیص خودکار مسیر</div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-1 rounded-xl">تکمیل</span>
                            </div>
                            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-emerald-500 w-[100%]"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest comments -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
            </svg>
          </span>
                        <h2 class="text-base font-extrabold">آخرین دیدگاه‌ها</h2>
                    </div>

                    <div class="mt-4 space-y-3">
                        <a href="#" class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-extrabold truncate">کاربر: Amir</div>
                                <div class="text-xs text-slate-500">۲ ساعت پیش</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                                «روی نسخه استیم هم جواب میده؟ چون مسیر نصب من فرق داره و ارور می‌گیرم…»
                            </div>
                            <div
                                class="mt-2 text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-flex">
                                روی: چک‌لیست نصب پچ
                            </div>
                        </a>

                        <a href="#" class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-extrabold truncate">کاربر: Nima</div>
                                <div class="text-xs text-slate-500">دیروز</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                                «بعد از آپدیت FPS بهتر شده، ولی وقتی Alt+Tab می‌کنم یه لگ کوتاه میاد…»
                            </div>
                            <div
                                class="mt-2 text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-flex">
                                روی: تنظیمات FPS بهتر
                            </div>
                        </a>

                        <a href="#" class="block rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-sm font-extrabold truncate">کاربر: Sara</div>
                                <div class="text-xs text-slate-500">۳ روز پیش</div>
                            </div>
                            <div class="mt-1 text-xs text-slate-500 leading-6 line-clamp-2">
                                «پیشنهاد می‌کنم یه گزینه برای بکاپ خودکار قبل از نصب اضافه کنید خیلی کاربردیه…»
                            </div>
                            <div
                                class="mt-2 text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl inline-flex">
                                روی: بکاپ قبل از آپدیت
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Optional ads -->
                <div class="rounded-xl2 border border-dashed border-slate-300 bg-white p-5">
                    <div class="font-extrabold text-slate-700">جایگاه تبلیغات</div>
                    <div class="mt-2 text-sm text-slate-500">بنر یا باکس اسپانسر (۳۰۰×۲۵۰)</div>
                    <div class="mt-1 text-xs text-slate-400">در نسخه نهایی می‌توان اسلات/کامپوننت کرد.</div>
                </div>

            </aside>
        </section>
    </main>
@endsection
