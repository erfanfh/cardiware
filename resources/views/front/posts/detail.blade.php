@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">

        <!-- Breadcrumb + Title strip -->
        <section class="mb-6">
            <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
                <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                <nav class="text-xs text-slate-500 flex flex-wrap gap-2">
                    <a href="#" class="hover:text-brand-700 font-bold transition">خانه</a>
                    <span>—</span>
                    <a href="#" class="hover:text-brand-700 font-bold transition">نوشته‌ها</a>
                    <span>—</span>
                    <span class="text-slate-600 font-semibold">عنوان نوشته</span>
                </nav>

                <div class="mt-3">
                    <div
                        class="inline-flex items-center gap-2 text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl">
                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                        آپدیت پچ
                    </div>

                    <h1 class="mt-3 text-2xl sm:text-3xl font-black leading-tight">
                        لیست تغییرات نسخه 0.9.3 و بهبودهای عملکردی
                    </h1>

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-2"><span class="h-2 w-2 rounded bg-slate-300"></span>۰۷ بهمن ۱۴۰۴</span>
                        <span>•</span>
                        <span>۲.۴k بازدید</span>
                        <span>•</span>
                        <span>زمان مطالعه: ۳ دقیقه</span>

                        <span class="mr-auto"></span>

                        <a href="#comments"
                           class="inline-flex items-center gap-2 font-extrabold text-brand-700 hover:text-brand-800 transition">
                            رفتن به دیدگاه‌ها <span class="font-black">←</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Layout -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- MAIN -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Post card -->
                <article class="rounded-xl2 border border-slate-200 bg-white shadow-sm overflow-hidden">

                    <!-- image with spacing -->
                    <div class="p-5 pb-0 sm:p-6 sm:pb-0">
                        <div class="relative rounded-xl2 overflow-hidden border border-slate-200">
                            <img src="https://picsum.photos/seed/mw-single-hero/1400/820" alt=""
                                 class="w-full h-[220px] sm:h-[360px] object-cover"/>
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/15 to-transparent"></div>

                            <div class="absolute top-4 right-4 flex flex-wrap items-center gap-2">
                            <span
                                class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">نسخه: 0.9.3</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">ویندوز</span>
                                <span
                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">بهینه‌سازی</span>
                            </div>

                            <div class="absolute bottom-4 right-4 left-4 flex items-center justify-end gap-3">
                                <button
                                    class="inline-flex items-center gap-2 rounded-2xl bg-white/10 text-white border border-white/10 backdrop-blur px-4 py-2 text-sm font-extrabold hover:bg-white/15 transition">
                                    اشتراک‌گذاری
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2">
                                        <path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7"></path>
                                        <path d="M16 6l-4-4-4 4"></path>
                                        <path d="M12 2v14"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- body -->
                    <div class="p-5 sm:p-6">
                        <p class="text-sm leading-7 text-slate-700">
                            در این نسخه تمرکز اصلی روی پایداری، سرعت لود، کاهش کرش‌ها و سازگاری بهتر با برخی سیستم‌ها
                            بوده
                            است.
                            در ادامه تغییرات را به صورت دقیق مرور می‌کنیم.
                        </p>

                        <div class="mt-6 space-y-6">
                            <div>
                                <div class="text-base font-extrabold">مهم‌ترین تغییرات</div>
                                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                                    <li class="flex items-start gap-3"><span
                                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهبود سرعت لود اولیه و کاهش
                                        زمان ورود به منوها
                                    </li>
                                    <li class="flex items-start gap-3"><span
                                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>رفع کرش‌های مرتبط با
                                        Alt+Tab در
                                        برخی سیستم‌ها
                                    </li>
                                    <li class="flex items-start gap-3"><span
                                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهبود سازگاری با مودهای
                                        گرافیکی
                                        رایج
                                    </li>
                                    <li class="flex items-start gap-3"><span
                                            class="mt-2 h-2 w-2 rounded bg-brand-600"></span>بهینه‌سازی مصرف رم در
                                        صحنه‌های
                                        شلوغ
                                    </li>
                                </ul>
                            </div>

                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                                <div class="text-sm font-extrabold text-emerald-900">نکته مهم</div>
                                <p class="mt-1 text-sm text-emerald-800 leading-7">
                                    اگر از نسخه‌های قدیمی‌تر استفاده می‌کنید، قبل از آپدیت حتماً از تنظیمات و فایل‌های
                                    مهم
                                    بکاپ بگیرید.
                                </p>
                            </div>

                            <div>
                                <div class="text-base font-extrabold">تصویر نمونه</div>
                                <img class="mt-3 rounded-xl2 border border-slate-200 shadow-sm"
                                     src="https://picsum.photos/seed/mw-single-inline/1200/700" alt=""/>
                            </div>
                        </div>

                        <!-- DOWNLOADS (moved ABOVE tags, list-only, not segmented into parts) -->
                        <section x-data="downloadsBlock()"
                                 class="mt-6 rounded-xl2 border border-slate-200 bg-white overflow-hidden">
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                    <div class="flex items-center gap-3">
                  <span
                      class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <path d="M7 10l5 5 5-5"></path>
                      <path d="M12 15V3"></path>
                    </svg>
                  </span>
                                        <div>
                                            <div class="text-base font-extrabold">لینک‌های دانلود این نوشته</div>
                                            <div class="mt-1 text-xs text-slate-500">چندین Mirror برای یک فایل (در صورت
                                                کندی
                                                یکی دیگر را امتحان کنید)
                                            </div>
                                        </div>
                                    </div>

                                    <!-- password copy only (no showing MW-093) -->
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="copyPassword($event)"
                                            class="h-11 px-4 rounded-2xl bg-slate-900 text-white text-sm font-extrabold hover:opacity-95 transition inline-flex items-center gap-2">
                                            کپی رمز فایل
                                            <span
                                                class="inline-flex h-7 w-7 rounded-xl bg-white/10 border border-white/10 items-center justify-center">🔒</span>
                                        </button>

                                        <button
                                            @click="copyText('https://example.com/post/slug', $event)"
                                            class="h-11 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-brand-700 hover:bg-brand-50 hover:border-brand-200 transition inline-flex items-center gap-2">
                                            کپی لینک صفحه
                                            <span
                                                class="inline-flex h-7 w-7 rounded-xl bg-brand-50 border border-brand-100 items-center justify-center">⧉</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- unified list of mirrors -->
                                <div class="mt-5 space-y-2">
                                    <template x-for="(m, i) in mirrors" :key="'m'+i">
                                        <div
                                            class="rounded-2xl border border-slate-200 p-3 hover:bg-slate-50 transition">
                                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-extrabold truncate"
                                                          x-text="m.title"></span>
                                                        <span
                                                            class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                                                            x-text="m.badge"></span>
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-500" x-text="m.meta"></div>
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <a :href="m.url"
                                                       class="rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                                                        دانلود
                                                    </a>

                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- toast -->
                                <div x-show="toast.show" x-transition
                                     class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3 text-sm text-emerald-900 flex items-center justify-between">
                                    <div class="font-extrabold" x-text="toast.text"></div>
                                    <button @click="toast.show=false"
                                            class="h-9 w-9 rounded-2xl border border-emerald-200 bg-white hover:bg-emerald-50 transition grid place-items-center">
                                        ×
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="text-xs text-slate-500">
                                        اگر لینک‌ها مشکل داشتند، در دیدگاه‌ها گزارش دهید تا سریع‌تر Mirror جدید اضافه
                                        شود.
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="#"
                                           class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition">
                                            راهنمای نصب
                                        </a>
                                        <a href="#comments"
                                           class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-extrabold text-white hover:opacity-95 transition">
                                            گزارش مشکل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Tags (NOW after downloads) -->
                        <div class="mt-6 border-t border-slate-200 pt-5 flex flex-wrap items-center gap-2">
                            <span class="text-xs text-slate-500">برچسب‌ها:</span>
                            <a href="#"
                               class="text-[11px] font-extrabold text-brand-800 bg-brand-50 border border-brand-100 px-2 py-1 rounded-xl hover:bg-brand-100 transition">Patch</a>
                            <a href="#"
                               class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl hover:bg-slate-50 transition">Performance</a>
                            <a href="#"
                               class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl hover:bg-slate-50 transition">Fix</a>
                        </div>
                    </div>
                </article>

                <!-- Preview images slider -->
                <section class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
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
                                        class="h-56 sm:h-64 w-full rounded-xl2 border border-slate-200 object-cover"
                                        src="https://picsum.photos/seed/mw-g1/1200/800" alt=""></div>
                                <div class="swiper-slide"><img
                                        class="h-56 sm:h-64 w-full rounded-xl2 border border-slate-200 object-cover"
                                        src="https://picsum.photos/seed/mw-g2/1200/800" alt=""></div>
                                <div class="swiper-slide"><img
                                        class="h-56 sm:h-64 w-full rounded-xl2 border border-slate-200 object-cover"
                                        src="https://picsum.photos/seed/mw-g3/1200/800" alt=""></div>
                                <div class="swiper-slide"><img
                                        class="h-56 sm:h-64 w-full rounded-xl2 border border-slate-200 object-cover"
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

                <!-- Video gallery slider -->
                <section class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
          <span
              class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 7l-7 5 7 5V7z"></path>
              <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
            </svg>
          </span>
                        <h2 class="text-base font-extrabold">گالری ویدیویی</h2>
                    </div>

                    <div class="mt-4">
                        <div class="swiper mwVideos">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide">
                                    <div
                                        class="rounded-xl2 border border-slate-200 overflow-hidden bg-slate-900 shadow-sm">
                                        <div class="relative">
                                            <img src="https://picsum.photos/seed/mw-v1/1200/800"
                                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl">Trailer</span>
                                                <span
                                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">01:24</span>
                                            </div>
                                            <button
                                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition">
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
                                        class="rounded-xl2 border border-slate-200 overflow-hidden bg-slate-900 shadow-sm">
                                        <div class="relative">
                                            <img src="https://picsum.photos/seed/mw-v2/1200/800"
                                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl">Tutorial</span>
                                                <span
                                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">03:10</span>
                                            </div>
                                            <button
                                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition">
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
                                        class="rounded-xl2 border border-slate-200 overflow-hidden bg-slate-900 shadow-sm">
                                        <div class="relative">
                                            <img src="https://picsum.photos/seed/mw-v3/1200/800"
                                                 class="h-56 sm:h-64 w-full object-cover opacity-80" alt="">
                                            <div
                                                class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                                            <div class="absolute top-3 right-3 inline-flex items-center gap-2">
                                            <span
                                                class="text-[11px] font-extrabold text-slate-900 bg-white/90 px-2 py-1 rounded-xl">Showcase</span>
                                                <span
                                                    class="text-[11px] font-extrabold text-white bg-black/40 ring-1 ring-white/15 px-2 py-1 rounded-xl">02:02</span>
                                            </div>
                                            <button
                                                class="absolute bottom-3 right-3 inline-flex items-center gap-2 rounded-2xl bg-brand-600 px-3 py-2 text-sm font-extrabold text-white hover:bg-brand-700 transition">
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
                                    class="mwVideosPrev h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                                    <span class="text-slate-700">→</span>
                                </div>
                                <div class="mwVideosPagination flex items-center gap-2"></div>
                                <div
                                    class="mwVideosNext h-10 w-10 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center cursor-pointer">
                                    <span class="text-slate-700">←</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Related posts (NEW DESIGN, not repetitive) -->
                <section class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
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
                           class="rounded-xl2 border border-slate-200 bg-slate-900 overflow-hidden shadow-sm hover:shadow-soft transition group">
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

                <!-- Comments -->
                <section id="comments"
                         x-data="commentsBlock()"
                         class="rounded-xl2 border border-slate-200 bg-white shadow-sm overflow-hidden">

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
<span
    class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
 <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
   <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"></path>
 </svg>
</span>
                                <div>
                                    <div class="text-base font-extrabold">دیدگاه‌ها</div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-1 rounded-xl"
                                    x-text="comments.length"></span>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-500">مرتب‌سازی:</span>
                                <select x-model="sort"
                                        class="h-10 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 outline-none
              focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition">
                                    <option value="new">جدیدترین</option>
                                    <option value="top">محبوب‌ترین</option>
                                    <option value="high">بیشترین امتیاز</option>
                                </select>
                            </div>
                        </div>
                        <!-- Add comment -->
                        <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4 sm:p-5">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Name -->
                                <input required
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none
                    focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                                       placeholder="نام شما (الزامی)"/>

                                <!-- Email -->
                                <input type="email" required
                                       class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm outline-none
                    focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                                       placeholder="ایمیل شما (الزامی)"/>
                            </div>

                            <textarea rows="3"
                                      class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none
             focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                                      placeholder="دیدگاه شما…"></textarea>

                            <!-- Bottom row: Stars + Submit button -->
                            <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

                                <!-- Rating Stars -->
                                <div class="flex items-center gap-2"
                                     x-data="{ hoverRating: 0 }"
                                     @mouseleave="hoverRating = 0">

                                    <span class="text-xs text-slate-500 ml-2">امتیاز شما:</span>

                                    <template x-for="n in 5" :key="'rate'+n">
                                        <button
                                            type="button"
                                            @mouseenter="hoverRating = n"
                                            @click="newRating = n"
                                            class="p-0.5 rounded-lg transition-transform duration-200 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-brand-100"
                                        >
                                            <svg viewBox="0 0 20 20"
                                                 class="h-7 w-7 drop-shadow-sm"
                                                 :class="(hoverRating ? hoverRating : newRating) >= n ? 'text-amber-400' : 'text-slate-300'">
                                                <path fill="currentColor"
                                                      d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"/>
                                            </svg>
                                        </button>
                                    </template>

                                </div>

                                <!-- Submit -->
                                <button
                                    class="rounded-2xl bg-slate-900 text-white px-6 py-3 text-sm font-extrabold hover:opacity-95 transition">
                                    ارسال دیدگاه
                                </button>
                            </div>
                        </div>
                        <!-- list -->
                        <div class="mt-5 space-y-4">
                            <template x-for="c in sortedComments()" :key="c.id">
                                <div class="rounded-3xl border border-slate-200 bg-white p-4 sm:p-5">
                                    <div class="flex items-start justify-between gap-3">

                                        <div class="flex items-start gap-3 min-w-0 w-full">

                                            <!-- Avatar -->
                                            <div
                                                class="h-11 w-11 rounded-2xl bg-slate-100 border border-slate-200 grid place-items-center font-black text-slate-700 shrink-0"
                                                x-text="c.name.charAt(0)"></div>

                                            <div class="min-w-0 w-full">

                                                <!-- Header -->
                                                <div class="flex items-center justify-between gap-3">

                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="text-sm font-extrabold truncate"
                                                             x-text="c.name"></div>
                                                        <span class="text-xs text-slate-500" x-text="c.time"></span>
                                                    </div>

                                                    <!-- ⭐ Rating pushed fully to left -->
                                                    <div class="flex items-center gap-0.5">
                                                        <template x-for="n in 5" :key="'cstar'+c.id+'-'+n">
                                                            <svg viewBox="0 0 20 20"
                                                                 class="h-4 w-4"
                                                                 :class="c.rating >= n ? 'text-amber-400' : 'text-slate-300'">
                                                                <path fill="currentColor"
                                                                      d="M10 15.27l-5.18 2.73 0.99-5.82L1.62 7.9l5.84-.85L10 1.75l2.54 5.3 5.84.85-4.19 4.28.99 5.82z"/>
                                                            </svg>
                                                        </template>
                                                    </div>

                                                </div>

                                                <div class="mt-2 text-sm text-slate-700 leading-7"
                                                     x-text="c.text"></div>

                                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                                    <button
                                                        @click="toggleLike(c)"
                                                        class="h-10 px-3 rounded-2xl border transition inline-flex items-center gap-2 text-sm font-extrabold"
                                                        :class="c.liked ? 'border-brand-200 bg-brand-50 text-brand-700' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50'">
                                                        <span>♥</span>
                                                        <span x-text="c.likes"></span>
                                                    </button>

                                                    <button
                                                        @click="startReply(c)"
                                                        class="h-10 px-3 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold text-slate-800 hover:bg-slate-50 transition inline-flex items-center gap-2">
                                                        پاسخ <span class="text-slate-400">↩</span>
                                                    </button>
                                                </div>

                                                <!-- Reply box (wider) -->
                                                <div x-show="replyTo === c.id" x-transition
                                                     class="mt-4 w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <div class="text-sm font-extrabold text-slate-800">
                                                            پاسخ به <span class="text-brand-700" x-text="c.name"></span>
                                                        </div>
                                                        <button @click="replyTo=null"
                                                                class="h-9 w-9 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center">
                                                            ×
                                                        </button>
                                                    </div>

                                                    <textarea rows="3"
                                                              class="mt-3 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none
                                     focus:ring-4 focus:ring-brand-100 focus:border-brand-300 transition"
                                                              placeholder="پاسخ شما…"></textarea>

                                                    <div class="mt-3 flex items-center justify-end gap-2">
                                                        <button @click="replyTo=null"
                                                                class="h-10 px-4 rounded-2xl border border-slate-200 bg-white text-sm font-extrabold hover:bg-slate-50 transition">
                                                            لغو
                                                        </button>
                                                        <button
                                                            class="h-10 px-4 rounded-2xl bg-brand-500 text-white text-sm font-extrabold shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                                                            ارسال پاسخ
                                                        </button>
                                                    </div>
                                                </div>


                                                <div class="mt-4 space-y-3" x-show="c.replies?.length">
                                                    <template x-for="r in c.replies" :key="r.id">
                                                        <div
                                                            class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div class="flex items-center gap-2 min-w-0">
                                                                    <div
                                                                        class="h-9 w-9 rounded-2xl bg-white border border-slate-200 grid place-items-center font-black text-slate-700"
                                                                        x-text="r.name.charAt(0)"></div>
                                                                    <div class="min-w-0">
                                                                        <div class="text-sm font-extrabold truncate"
                                                                             x-text="r.name"></div>
                                                                        <div class="text-xs text-slate-500"
                                                                             x-text="r.time"></div>
                                                                    </div>
                                                                </div>
                                                                <span
                                                                    class="text-[11px] font-extrabold text-slate-700 bg-white border border-slate-200 px-2 py-1 rounded-xl">پاسخ</span>
                                                            </div>
                                                            <div class="mt-2 text-sm text-slate-700 leading-7"
                                                                 x-text="r.text"></div>
                                                        </div>
                                                    </template>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="hidden sm:flex flex-col items-end gap-2">

                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-xs text-slate-500">برای گزارش مشکل لینک‌ها، از بخش دانلود استفاده کنید.
                            </div>
                            <a href="#"
                               class="text-sm font-extrabold text-brand-700 hover:text-brand-800 transition inline-flex items-center gap-2">
                                قوانین دیدگاه‌ها <span class="font-black">←</span>
                            </a>
                        </div>
                    </div>
                </section>
            </div>

            <!-- SIDEBAR -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Tools (2 columns) -->
                <div class="rounded-xl2 border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center gap-3">
            <span
                class="inline-flex h-9 w-9 rounded-2xl bg-brand-50 border border-brand-100 text-brand-700 items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M14.7 6.3a1 1 0 0 0-1.4 0l-7 7a1 1 0 0 0 0 1.4l2 2a1 1 0 0 0 1.4 0l7-7a1 1 0 0 0 0-1.4z"></path>
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
