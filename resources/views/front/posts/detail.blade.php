@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.posts.partials.detail.title')

        <section class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                @include('front.posts.partials.detail.content')
                @include('front.posts.partials.detail.image-gallery')
                @include('front.posts.partials.detail.video')
                @include('front.posts.partials.detail.related-posts')
                @include('front.posts.partials.detail.comments')
            </div>

            <aside class="lg:col-span-3 space-y-6">

                <!-- Tools (2 columns) -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
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
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-5">
                    <div class="font-extrabold text-slate-700">جایگاه تبلیغات</div>
                    <div class="mt-2 text-sm text-slate-500">بنر یا باکس اسپانسر (۳۰۰×۲۵۰)</div>
                    <div class="mt-1 text-xs text-slate-400">در نسخه نهایی می‌توان اسلات/کامپوننت کرد.</div>
                </div>

            </aside>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pillify = (selector) => {
                const bullets = document.querySelectorAll(selector + ' .swiper-pagination-bullet');
                bullets.forEach(b => b.classList.remove('w-8'));
                const active = document.querySelector(selector + ' .swiper-pagination-bullet-active');
                if (active) active.classList.add('w-8');
            };

            new Swiper('.mwGallery', {
                loop: true,
                spaceBetween: 14,
                slidesPerView: 1.1,
                breakpoints: {640: {slidesPerView: 1.2}, 1024: {slidesPerView: 1.6}},
                navigation: {nextEl: '.mwGalleryNext', prevEl: '.mwGalleryPrev'},
                pagination: {
                    el: '.mwGalleryPagination',
                    clickable: true,
                    renderBullet: (index, className) => `<span class="${className} h-2 ${index === 0 ? 'w-8' : 'w-2'} rounded-full bg-slate-300 inline-block"></span>`
                },
                on: {
                    init() {
                        pillify('.mwGalleryPagination');
                    }, slideChange() {
                        pillify('.mwGalleryPagination');
                    }
                }
            });

            new Swiper('.mwVideos', {
                loop: true,
                spaceBetween: 14,
                slidesPerView: 1.05,
                breakpoints: {640: {slidesPerView: 1.2}, 1024: {slidesPerView: 1.4}},
                navigation: {nextEl: '.mwVideosNext', prevEl: '.mwVideosPrev'},
                pagination: {
                    el: '.mwVideosPagination',
                    clickable: true,
                    renderBullet: (index, className) => `<span class="${className} h-2 ${index === 0 ? 'w-8' : 'w-2'} rounded-full bg-slate-300 inline-block"></span>`
                },
                on: {
                    init() {
                        pillify('.mwVideosPagination');
                    }, slideChange() {
                        pillify('.mwVideosPagination');
                    }
                }
            });
        });

        function downloadsBlock() {
            return {
                toast: {show: false, text: ''},
                mirrors: [
                    {
                        title: 'دانلود از سرور اصلی',
                        badge: 'Main',
                        meta: 'Windows • 1.2GB • سرعت بالا',
                        url: 'https://example.com/download/main'
                    },
                    {
                        title: 'دانلود از Mirror 1',
                        badge: 'Mirror',
                        meta: 'Windows • 1.2GB • جایگزین',
                        url: 'https://example.com/download/m1'
                    },
                    {
                        title: 'دانلود از Mirror 2',
                        badge: 'Mirror',
                        meta: 'Windows • 1.2GB • جایگزین',
                        url: 'https://example.com/download/m2'
                    },
                    {
                        title: 'دانلود از Mirror 3',
                        badge: 'Mirror',
                        meta: 'Windows • 1.2GB • جایگزین',
                        url: 'https://example.com/download/m3'
                    },
                    {
                        title: 'دانلود از Mirror 4',
                        badge: 'Mirror',
                        meta: 'Windows • 1.2GB • جایگزین',
                        url: 'https://example.com/download/m4'
                    },
                ],
                copyText(text) {
                    navigator.clipboard?.writeText(text);
                    this.toast.text = 'کپی شد ✅';
                    this.toast.show = true;
                    setTimeout(() => this.toast.show = false, 1600);
                },
                copyPassword() {
                    // password is NOT shown anywhere; only copied
                    const pass = 'MW-093';
                    this.copyText(pass);
                    this.toast.text = 'رمز فایل کپی شد ✅';
                }
            }
        }

        function commentsBlock() {
            return {
                sort: 'new',
                newRating: 5,
                replyTo: null,
                comments: [
                    {
                        id: 1, name: 'Amir', time: '۲ ساعت پیش', rating: 5, likes: 12, liked: false,
                        text: 'روی نسخه استیم هم جواب میده؟ چون مسیر نصب من فرق داره و ارور می‌گیرم…',
                        replies: [{
                            id: '1r1',
                            name: 'ModdingWay',
                            time: '۱ ساعت پیش',
                            text: 'بله، فقط مسیر نصب را دقیق انتخاب کنید. اگر ارور داشتید، لاگ را ارسال کنید.'
                        }]
                    },
                    {
                        id: 2,
                        name: 'Nima',
                        time: 'دیروز',
                        rating: 4,
                        likes: 7,
                        liked: false,
                        text: 'بعد از آپدیت FPS بهتر شده، ولی وقتی Alt+Tab می‌کنم یه لگ کوتاه میاد…',
                        replies: []
                    },
                    {
                        id: 3,
                        name: 'Sara',
                        time: '۳ روز پیش',
                        rating: 5,
                        likes: 19,
                        liked: false,
                        text: 'پیشنهاد می‌کنم گزینه بکاپ خودکار قبل از نصب اضافه کنید خیلی کاربردیه…',
                        replies: []
                    },
                ],
                sortedComments() {
                    const arr = [...this.comments];
                    if (this.sort === 'top') return arr.sort((a, b) => b.likes - a.likes);
                    if (this.sort === 'high') return arr.sort((a, b) => b.rating - a.rating);
                    return arr;
                },
                toggleLike(c) {
                    c.liked = !c.liked;
                    c.likes += c.liked ? 1 : -1;
                },
                startReply(c) {
                    this.replyTo = (this.replyTo === c.id) ? null : c.id;
                },
                copyText(text) {
                    navigator.clipboard?.writeText(text);
                }
            }
        }
    </script>
@endpush
