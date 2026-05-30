@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8" x-data="productPage()"
          x-init="init()">
        <section data-smart-sidebar-layout class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div data-smart-sidebar-content class="lg:col-span-7 space-y-6">
                @include('front.catalog.products.partials.detail.title')
                @include('front.catalog.products.partials.detail.share-box')
                @include('front.catalog.products.partials.detail.image-gallery')

                <!-- tabs nav / anchor nav -->
                <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sticky top-[98px] z-20">
                    <div class="flex gap-2 overflow-x-auto hide-scrollbar pb-1">
                        <template x-for="item in sections" :key="item.id">
                            <button
                                type="button"
                                @click="scrollToSection(item.id)"
                                class="shrink-0 h-10 px-4 rounded-2xl border font-extrabold text-sm transition"
                                :class="activeSection === item.id
                    ? 'border-brand-100 bg-brand-50 text-slate-900'
                    : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50'"
                                x-text="item.label"
                            ></button>
                        </template>
                    </div>
                </section>

                @include('front.catalog.products.partials.detail.content')
                @include('front.catalog.products.partials.detail.installation-steps')
                @include('front.catalog.products.partials.detail.faqs')
                @include('front.catalog.products.partials.detail.releases')
            </div>

            <div data-smart-sidebar-column class="lg:col-span-3">
                <aside data-smart-sidebar-box class="space-y-6">
                    <!-- price box -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">خرید محصول</div>
                                <div class="text-xs text-slate-500 mt-1">به همراه آپشن‌های قابل انتخاب</div>
                            </div>

                            <div class="text-left">
                                <div class="text-xs text-slate-400 line-through"
                                     x-text="formatPrice(baseOriginalPrice)"></div>
                                <div class="text-2xl font-black text-brand-700"
                                     x-text="formatPrice(totalPrice)"></div>
                            </div>
                        </div>

                        <!-- preorder -->
                        <div class="mt-5 rounded-3xl border border-brand-100 bg-brand-50 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-extrabold text-brand-800">این محصول در حالت پیش‌فروش
                                        است
                                    </div>
                                    <div class="mt-1 text-xs text-slate-600 leading-6">
                                        انتشار نهایی در تاریخ مشخص انجام می‌شود و خریداران پس از انتشار، فایل کامل
                                        را دریافت می‌کنند.
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-brand-800 bg-white border border-brand-100 px-2 py-1 rounded-xl">Pre-order</span>
                            </div>

                            <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                                <template x-for="part in preorderCountdown" :key="part.label">
                                    <div class="rounded-2xl bg-white border border-brand-100 p-2">
                                        <div class="text-base font-black text-brand-700" x-text="part.value"></div>
                                        <div class="text-[11px] font-extrabold text-slate-500 mt-1"
                                             x-text="part.label"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- discount timer -->
                        <div class="mt-4 rounded-3xl border border-emerald-200 bg-emerald-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-extrabold text-emerald-800">تخفیف جشنواره فعال است
                                    </div>
                                    <div class="mt-1 text-xs text-slate-600">این تخفیف تا پایان زمان زیر فعال خواهد
                                        بود.
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-extrabold text-emerald-700 bg-white border border-emerald-200 px-2 py-1 rounded-xl">٪۲۰ تخفیف</span>
                            </div>

                            <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                                <template x-for="part in discountCountdown" :key="'d-' + part.label">
                                    <div class="rounded-2xl bg-white border border-emerald-200 p-2">
                                        <div class="text-base font-black text-emerald-700"
                                             x-text="part.value"></div>
                                        <div class="text-[11px] font-extrabold text-slate-500 mt-1"
                                             x-text="part.label"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- options -->
                        <div class="mt-5">
                            <div class="text-sm font-extrabold text-slate-900 mb-3">آپشن‌های همراه خرید</div>

                            <div class="space-y-3">
                                <template x-for="option in options" :key="option.id">
                                    <label
                                        class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4 hover:bg-slate-50 transition cursor-pointer">
                                        <input type="checkbox" class="mt-1 accent-orange-500"
                                               x-model="option.checked" @change="recalculatePrice()">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="text-sm font-extrabold text-slate-900"
                                                     x-text="option.title"></div>
                                                <div class="text-sm font-black text-brand-700"
                                                     x-text="option.price === 0 ? 'رایگان' : '+ ' + formatPrice(option.price)"></div>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-500 leading-6"
                                                 x-text="option.desc"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <a href="#"
                               class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 active:bg-brand-700 transition">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="text-white">
                                    <path d="M6 7h14l-1.5 12h-11L6 7Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M9 7a3 3 0 0 1 6 0" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                افزودن به سبد خرید
                            </a>

                            <a href="#"
                               class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-slate-200 text-brand-700 px-5 py-3 text-sm font-extrabold hover:bg-brand-50 hover:border-brand-200 transition">
                                خرید با لینک اختصاصی
                            </a>
                        </div>
                    </section>

                    <!-- update progress -->
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

                    <!-- product info -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-extrabold text-slate-900 mb-4">باکس اطلاعات محصول</div>

                        <div class="space-y-3">
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">نام محصول</div>
                                <div class="text-sm font-bold text-slate-900">پچ حرفه‌ای لیگ ایران برای FC 26</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تاریخ انتشار</div>
                                <div class="text-sm font-bold text-slate-900">۱۴۰۴/۱۲/۲۸</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تعداد بازدید</div>
                                <div class="text-sm font-bold text-slate-900">۱۲,۴۸۰</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تعداد فروش</div>
                                <div class="text-sm font-bold text-slate-900">۱,۳۱۰</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تاریخ آپدیت</div>
                                <div class="text-sm font-bold text-slate-900">۱۴۰۵/۰۱/۰۲</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تاریخ آپدیت بعدی</div>
                                <div class="text-sm font-bold text-slate-900">۱۴۰۵/۰۱/۱۰</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">تیم سازنده</div>
                                <div class="text-sm font-bold text-slate-900">ModdingWay Studio</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">قابل نصب در</div>
                                <div class="text-sm font-bold text-slate-900">FC 26 - PC</div>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">فروشنده</div>
                                <a href="#"
                                   class="text-sm font-bold text-brand-700 hover:text-brand-800 transition">مشاهده
                                    صفحه فروشنده ←</a>
                            </div>

                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-3">
                                <div class="text-xs font-extrabold text-slate-500">شناسه محصول</div>
                                <div class="text-sm font-bold text-slate-900">MW-FC26-IRPL-0481</div>
                            </div>
                        </div>
                    </section>

                    <!-- tags -->
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="text-sm font-extrabold text-slate-900 mb-4">برچسب‌ها</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in tags" :key="tag">
                                <a href="#"
                                   class="text-[11px] font-extrabold text-slate-700 bg-slate-100 border border-slate-200 px-3 py-2 rounded-xl hover:bg-brand-50 hover:border-brand-100 hover:text-brand-800 transition"
                                   x-text="tag"></a>
                            </template>
                        </div>
                    </section>

                    <!-- support card -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm relative overflow-hidden">
                        <div
                            class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
                        <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

                        <div class="relative">
                            <div class="text-sm font-extrabold text-slate-900">نیاز به راهنمایی قبل از خرید داری؟
                            </div>
                            <p class="mt-2 text-sm text-slate-600 leading-7">
                                اگر در انتخاب نسخه مناسب، روش نصب یا سازگاری محصول سوال داری، از طریق تیکت یا پیام
                                مستقیم با پشتیبانی در ارتباط باش.
                            </p>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <a href="#"
                                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 text-white px-4 py-3 text-sm font-extrabold hover:opacity-95 transition">
                                    ثبت تیکت
                                </a>
                                <a href="#"
                                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-slate-200 text-brand-700 px-4 py-3 text-sm font-extrabold hover:bg-brand-50 transition">
                                    پیام به فروشنده
                                </a>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script>
        function productPage() {
            return {
                activeHero: 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',

                gallery: [
                    'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=1200&q=80'
                ],

                gallerySet1: [
                    'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1560253023-3ec5d502959f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=900&q=80'
                ],

                gallerySet2: [
                    'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1543357480-c60d40007a3f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1508098682722-e99c643e7485?auto=format&fit=crop&w=900&q=80'
                ],

                previewGrid: [
                    'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1543357480-c60d40007a3f?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1508098682722-e99c643e7485?auto=format&fit=crop&w=900&q=80'
                ],

                videos: [
                    {
                        title: 'تریلر معرفی نسخه جدید لیگ ایران',
                        meta: 'نمایش فضای کلی محصول و بخش‌های جدید',
                        duration: '02:45',
                        cover: 'https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=1200&q=80'
                    },
                    {
                        title: 'پیشنمایش استادیوم‌ها و اسکوربورد',
                        meta: 'نمونه کیفیت گرافیکی بازی پس از نصب',
                        duration: '01:56',
                        cover: 'https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?auto=format&fit=crop&w=1200&q=80'
                    },
                    {
                        title: 'آموزش نصب سریع روی نسخه PC',
                        meta: 'آموزش مرحله‌ای و رفع خطاهای ابتدایی',
                        duration: '04:10',
                        cover: 'https://images.unsplash.com/photo-1511882150382-421056c89033?auto=format&fit=crop&w=1200&q=80'
                    }
                ],

                sections: [
                    {id: 'desc', label: 'توضیحات کلی'},
                    {id: 'install', label: 'آموزش نصب'},
                    {id: 'filesize', label: 'حجم فایل‌ها'},
                    {id: 'faq', label: 'سوالات متداول'},
                    {id: 'previews', label: 'پیشنمایش‌ها'},
                    {id: 'videos', label: 'ویدیوها'},
                    {id: 'changes', label: 'لیست تغییرات'},
                    {id: 'team', label: 'اعضای سازنده'},
                    {id: 'comments', label: 'کامنت‌ها'},
                    {id: 'related', label: 'محصولات مشابه'}
                ],

                activeSection: 'desc',

                installSteps: [
                    {
                        title: 'فایل‌ها را دانلود و استخراج کنید',
                        text: 'پس از خرید، همه فایل‌های مربوط به نسخه اصلی و آموزش را دریافت می‌کنید. ابتدا فایل‌ها را در یک پوشه مجزا استخراج کنید.'
                    },
                    {
                        title: 'از فایل‌های فعلی بازی بکاپ بگیرید',
                        text: 'پیشنهاد می‌شود قبل از هر تغییری، از پوشه‌های اصلی بازی یک نسخه پشتیبان تهیه کنید تا در صورت نیاز بتوانید به حالت قبل بازگردید.'
                    },
                    {
                        title: 'فایل‌های پچ را در مسیر صحیح کپی کنید',
                        text: 'طبق آموزش داخل بسته، فایل‌ها را در مسیر مشخص شده قرار دهید و ساختار پوشه‌ها را بدون تغییر حفظ کنید.'
                    },
                    {
                        title: 'فایل تنظیمات و آپدیت مکمل را اعمال کنید',
                        text: 'در پایان، فایل تنظیمات و در صورت وجود آپدیت مکمل را نصب کنید تا تمام بخش‌ها به درستی فعال شوند.'
                    }
                ],

                files: [
                    {name: 'فایل اصلی پچ', desc: 'نسخه کامل محصول', size: '8.2GB'},
                    {name: 'فایل آموزش نصب', desc: 'آموزش PDF + ویدیو', size: '420MB'},
                    {name: 'آپدیت مکمل 4.8.1', desc: 'اصلاحات جزئی و به‌روزرسانی ترکیب‌ها', size: '760MB'}
                ],

                faqs: [
                    {
                        q: 'آیا این محصول روی نسخه کرک‌شده هم نصب می‌شود؟',
                        a: 'بسته به نسخه بازی و نوع فایل‌ها ممکن است شرایط متفاوت باشد، اما پیشنهاد اصلی استفاده روی نسخه‌ای است که با محصول تست شده است.',
                        open: true
                    },
                    {
                        q: 'بعد از خرید، آپدیت‌های بعدی هم دریافت می‌کنم؟',
                        a: 'بله، در صورت اعلام فروشنده و فعال بودن پشتیبانی محصول، آپدیت‌های مرتبط از پنل کاربری در دسترس قرار می‌گیرند.',
                        open: false
                    },
                    {
                        q: 'اگر هنگام نصب به خطا خوردم چه کنم؟',
                        a: 'ابتدا آموزش نصب و بخش رفع خطا را بررسی کنید. در صورت حل نشدن، از طریق تیکت با ذکر جزئیات سیستم و خطا ارتباط بگیرید.',
                        open: false
                    }
                ],

                changelogs: [
                    {
                        version: 'آپدیت 4.8.1',
                        date: '۱۴۰۵/۰۱/۰۲',
                        subtitle: 'بهبود پایداری و اصلاح چند بخش مهم',
                        open: true,
                        items: [
                            'به‌روزرسانی ترکیب تیم‌ها و نقل‌وانتقالات',
                            'اصلاح چند کیت و فونت شماره پیراهن',
                            'رفع مشکل نمایش بنر در برخی استادیوم‌ها'
                        ]
                    },
                    {
                        version: 'آپدیت 4.8.0',
                        date: '۱۴۰۴/۱۲/۲۸',
                        subtitle: 'انتشار نسخه اصلی جدید',
                        open: false,
                        items: [
                            'افزودن ساختار کامل لیگ ایران',
                            'طراحی اسکوربورد اختصاصی جدید',
                            'افزودن فیس چند بازیکن جدید',
                            'بهینه‌سازی منوها و آیتم‌های گرافیکی'
                        ]
                    }
                ],

                teamMembers: [
                    {
                        name: 'محمد یوسفی',
                        role: 'مدیر پروژه',
                        avatar: 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80',
                        bio: 'مسئول هماهنگی کلی تیم، مدیریت انتشار، ساختار محصول و ارتباط با کاربران.',
                        skill: 'مدیریت محصول و هماهنگی تیم',
                        exp: '۵ سال'
                    },
                    {
                        name: 'علی رضایی',
                        role: 'طراح گرافیک بازی',
                        avatar: 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80',
                        bio: 'طراحی منوها، اسکوربوردها، کیت‌ها و آیتم‌های بصری اختصاصی.',
                        skill: 'گرافیک و UI داخل بازی',
                        exp: '۴ سال'
                    },
                    {
                        name: 'امیر احمدی',
                        role: 'ادیتور دیتابیس',
                        avatar: 'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=400&q=80',
                        bio: 'ویرایش ترکیب‌ها، بازیکنان، لیگ‌ها و داده‌های ساختاری محصول.',
                        skill: 'دیتابیس و تنظیمات تیم‌ها',
                        exp: '۳ سال'
                    },
                    {
                        name: 'سارا کیانی',
                        role: 'پشتیبانی نصب',
                        avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=400&q=80',
                        bio: 'پاسخ‌گویی به تیکت‌ها، راهنمایی کاربران و بررسی مشکلات نصب.',
                        skill: 'پشتیبانی فنی',
                        exp: '۲ سال'
                    }
                ],

                memberModalOpen: false,
                selectedMember: {},

                comments: [
                    {
                        name: 'کاربر FC Lover',
                        date: '۱۴۰۵/۰۱/۰۳',
                        avatar: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=300&q=80',
                        text: 'نصب خیلی راحت بود و آموزش داخل بسته واقعاً کمک کرد. کیفیت گرافیک و اسکوربوردها عالی بود و روی سیستم من بدون مشکل اجرا شد.'
                    },
                    {
                        name: 'امیرحسین',
                        date: '۱۴۰۵/۰۱/۰۴',
                        avatar: 'https://images.unsplash.com/photo-1502767089025-6572583495b0?auto=format&fit=crop&w=300&q=80',
                        text: 'ترکیب تیم‌ها و فضای لیگ ایران خیلی خوب کار شده. امیدوارم آپدیت بعدی فیس چند بازیکن دیگر هم اضافه شود.'
                    }
                ],

                relatedProducts: [
                    {
                        title: 'آپدیت نقل‌وانتقالات FC 26',
                        meta: 'FC 26 • فایل مکمل • نسخه جدید',
                        price: '۳۹۹,۰۰۰ تومان',
                        badge: 'مکمل',
                        cover: 'https://images.unsplash.com/photo-1517466787929-bc90951d0974?auto=format&fit=crop&w=900&q=80'
                    },
                    {
                        title: 'پکیج فیس و گرافیک ویژه FC',
                        meta: 'فیس، کیت، منو و اسکوربورد',
                        price: '۵۹۹,۰۰۰ تومان',
                        badge: 'ویژه',
                        cover: 'https://images.unsplash.com/photo-1545239351-1141bd82e8a6?auto=format&fit=crop&w=900&q=80'
                    },
                    {
                        title: 'ماد استادیوم‌های ایران',
                        meta: 'استادیوم و نورپردازی اختصاصی',
                        price: '۴۴۹,۰۰۰ تومان',
                        badge: 'پرطرفدار',
                        cover: 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?auto=format&fit=crop&w=900&q=80'
                    }
                ],

                tags: ['فیفا', 'FC 26', 'لیگ ایران', 'پچ', 'دانلودی', 'آپدیت', 'آموزش نصب'],

                baseOriginalPrice: 1490000,
                basePrice: 1190000,
                totalPrice: 1190000,

                options: [
                    {
                        id: 1,
                        title: 'نصب حضوری / ریموت',
                        desc: 'هماهنگی برای نصب توسط تیم پشتیبانی به‌صورت ریموت',
                        price: 350000,
                        checked: false
                    },
                    {
                        id: 2,
                        title: 'بسته مکمل گرافیکی',
                        desc: 'شامل چند فایل گرافیکی تکمیلی و بهینه‌سازی نمایشی',
                        price: 180000,
                        checked: false
                    },
                    {
                        id: 3,
                        title: 'راهنمای ویدیویی پیشرفته',
                        desc: 'نسخه کامل‌تر آموزش و نکات حرفه‌ای نصب و نگهداری',
                        price: 0,
                        checked: true
                    }
                ],

                preorderTarget: new Date('2026-03-31T21:00:00'),
                discountTarget: new Date('2026-03-29T23:59:59'),

                preorderCountdown: [
                    {label: 'روز', value: '00'},
                    {label: 'ساعت', value: '00'},
                    {label: 'دقیقه', value: '00'},
                    {label: 'ثانیه', value: '00'}
                ],

                discountCountdown: [
                    {label: 'روز', value: '00'},
                    {label: 'ساعت', value: '00'},
                    {label: 'دقیقه', value: '00'},
                    {label: 'ثانیه', value: '00'}
                ],

                updateStart: new Date('2026-03-20T00:00:00'),
                updateEnd: new Date('2026-03-31T00:00:00'),
                updateProgress: 0,

                init() {
                    this.recalculatePrice();
                    this.startCountdowns();
                    this.computeUpdateProgress();
                    this.observeSections();

                    this.$nextTick(() => {
                        new Swiper('.productGallerySlider', {
                            slidesPerView: 1,
                            spaceBetween: 16,
                            navigation: {
                                nextEl: '.gallery-next',
                                prevEl: '.gallery-prev',
                            }
                        });

                        new Swiper('.productVideoSlider', {
                            slidesPerView: 1.08,
                            spaceBetween: 16,
                            breakpoints: {
                                640: {slidesPerView: 1.4},
                                1024: {slidesPerView: 2}
                            },
                            navigation: {
                                nextEl: '.video-next',
                                prevEl: '.video-prev',
                            }
                        });
                    });
                },

                recalculatePrice() {
                    let total = this.basePrice;
                    this.options.forEach(option => {
                        if (option.checked) total += option.price;
                    });
                    this.totalPrice = total;
                },

                formatPrice(number) {
                    return new Intl.NumberFormat('fa-IR').format(number) + ' تومان';
                },

                startCountdowns() {
                    const tick = () => {
                        this.preorderCountdown = this.getRemaining(this.preorderTarget);
                        this.discountCountdown = this.getRemaining(this.discountTarget);
                        this.computeUpdateProgress();
                    };

                    tick();
                    setInterval(tick, 1000);
                },

                getRemaining(target) {
                    const now = new Date();
                    let diff = target - now;

                    if (diff < 0) diff = 0;

                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const minutes = Math.floor((diff / (1000 * 60)) % 60);
                    const seconds = Math.floor((diff / 1000) % 60);

                    return [
                        {label: 'روز', value: String(days).padStart(2, '0')},
                        {label: 'ساعت', value: String(hours).padStart(2, '0')},
                        {label: 'دقیقه', value: String(minutes).padStart(2, '0')},
                        {label: 'ثانیه', value: String(seconds).padStart(2, '0')}
                    ];
                },

                computeUpdateProgress() {
                    const now = new Date().getTime();
                    const start = this.updateStart.getTime();
                    const end = this.updateEnd.getTime();

                    if (now <= start) {
                        this.updateProgress = 5;
                        return;
                    }

                    if (now >= end) {
                        this.updateProgress = 100;
                        return;
                    }

                    const percentage = ((now - start) / (end - start)) * 100;
                    this.updateProgress = Math.max(5, Math.min(100, Math.round(percentage)));
                },

                toggleFaq(index) {
                    this.faqs[index].open = !this.faqs[index].open;
                },

                openMember(member) {
                    this.selectedMember = member;
                    this.memberModalOpen = true;
                    document.body.style.overflow = 'hidden';
                },

                closeMember() {
                    this.memberModalOpen = false;
                    document.body.style.overflow = '';
                },

                scrollToSection(id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    this.activeSection = id;
                    el.scrollIntoView({behavior: 'smooth', block: 'start'});
                },

                observeSections() {
                    const options = {
                        root: null,
                        rootMargin: '-120px 0px -55% 0px',
                        threshold: 0
                    };

                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                this.activeSection = entry.target.id;
                            }
                        });
                    }, options);

                    this.sections.forEach(section => {
                        const el = document.getElementById(section.id);
                        if (el) observer.observe(el);
                    });
                }
            }
        }
    </script>
@endpush
