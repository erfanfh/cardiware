<section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 [&_svg]:w-6 [&_svg]:h-6">
    <!-- Downloads -->
    <button
        type="button"
        @click="setTab('downloads')"
        class="group relative overflow-hidden rounded-3xl border border-emerald-100 bg-white p-5 text-right shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft cursor-pointer"
    >
        <div
            class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-emerald-100/70 blur-2xl transition group-hover:bg-emerald-200/80"></div>

        <div class="relative flex items-start justify-between gap-3">
            <div
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                @include('icons.cloud-download')
            </div>

            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-extrabold text-emerald-700">
                آماده دریافت
            </span>
        </div>

        <div class="relative mt-5">
            <div class="text-sm font-extrabold text-slate-900">دانلودهای آماده</div>
            <div class="mt-1 text-xs leading-5 text-slate-500">فایل‌های قابل دریافت</div>

            <div class="mt-5 flex items-end justify-between gap-3">
                <div class="text-3xl font-black text-slate-900">0</div>

                <div
                    class="flex items-center [&_svg]:!w-[14px] [&_svg]:!h-[14px] gap-1 text-xs font-extrabold text-emerald-600 transition">
                    مشاهده
                    @include('icons.arrow-left-02')
                </div>
            </div>
        </div>
    </button>

    <!-- Orders -->
    <button
        type="button"
        @click="setTab('orders')"
        class="group relative overflow-hidden rounded-3xl border border-orange-100 bg-white p-5 text-right shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft cursor-pointer"
    >
        <div
            class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-orange-100/70 blur-2xl transition group-hover:bg-orange-200/80"></div>

        <div class="relative flex items-start justify-between gap-3">
            <div
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-orange-100 bg-orange-50 text-orange-600">
                @include('icons.shopping-basket-03')
            </div>

            <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-extrabold text-orange-700">
                پرداخت موفق
            </span>
        </div>

        <div class="relative mt-5">
            <div class="text-sm font-extrabold text-slate-900">سفارش‌های تکمیل شده</div>
            <div class="mt-1 text-xs leading-5 text-slate-500">سفارش‌های موفق فروشگاه</div>

            <div class="mt-5 flex items-end justify-between gap-3">
                <div class="text-3xl font-black text-slate-900">{{ $user->paid_orders_count }}</div>

                <div
                    class="flex items-center [&_svg]:!w-[14px] [&_svg]:!h-[14px] gap-1 text-xs font-extrabold text-orange-600 transition">
                    مشاهده
                    @include('icons.arrow-left-02')
                </div>
            </div>
        </div>
    </button>

    <!-- Tickets -->
    <button
        type="button"
        @click="setTab('tickets')"
        class="group relative overflow-hidden rounded-3xl border border-cyan-100 bg-white p-5 text-right shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft cursor-pointer"
    >
        <div
            class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-cyan-100/70 blur-2xl transition group-hover:bg-cyan-200/80"></div>

        <div class="relative flex items-start justify-between gap-3">
            <div
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-cyan-100 bg-cyan-50 text-cyan-600">
                @include('icons.message-search-02')
            </div>

            <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-[11px] font-extrabold text-cyan-700">
                نیازمند پیگیری
            </span>
        </div>

        <div class="relative mt-5">
            <div class="text-sm font-extrabold text-slate-900">تیکت‌های فعال</div>
            <div class="mt-1 text-xs leading-5 text-slate-500">در انتظار پاسخ یا پیگیری</div>

            <div class="mt-5 flex items-end justify-between gap-3">
                <div class="text-3xl font-black text-slate-900">0</div>

                <div
                    class="flex items-center [&_svg]:!w-[14px] [&_svg]:!h-[14px] gap-1 text-xs font-extrabold text-cyan-600 transition">
                    پیگیری
                    @include('icons.arrow-left-02')
                </div>
            </div>
        </div>
    </button>

    <!-- Favorites -->
    <button
        type="button"
        @click="setTab('favorites')"
        class="group relative overflow-hidden rounded-3xl border border-rose-100 bg-white p-5 text-right shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-soft cursor-pointer"
    >
        <div
            class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-rose-100/70 blur-2xl transition group-hover:bg-rose-200/80"></div>

        <div class="relative flex items-start justify-between gap-3">
            <div
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-600">
                @include('icons.favourite')
            </div>

            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-extrabold text-rose-700">
                ذخیره‌شده
            </span>
        </div>

        <div class="relative mt-5">
            <div class="text-sm font-extrabold text-slate-900">علاقه‌مندی‌ها</div>
            <div class="mt-1 text-xs leading-5 text-slate-500">محصولات ذخیره‌شده برای بعد</div>

            <div class="mt-5 flex items-end justify-between gap-3">
                <div class="text-3xl font-black text-slate-900">0</div>

                <div
                    class="flex items-center [&_svg]:!w-[14px] [&_svg]:!h-[14px] gap-1 text-xs font-extrabold text-rose-600 transition">
                    مشاهده
                    @include('icons.arrow-left-02')
                </div>
            </div>
        </div>
    </button>
</section>

<section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-base font-extrabold text-slate-900">فعالیت اخیر</div>
        <div class="mt-5 space-y-3">
            <div
                class="rounded-3xl border border-dashed border-slate-300 bg-white p-5 text-center text-sm text-slate-500">
                فعالیتی یافت نشد!
            </div>
            {{--            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">--}}
            {{--                <div class="text-sm font-bold text-slate-800">دانلود پچ لیگ ایران برای FC26</div>--}}
            {{--                <div class="mt-1 text-xs text-slate-500">امروز - ساعت ۱۴:۳۰</div>--}}
            {{--            </div>--}}
            {{--            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">--}}
            {{--                <div class="text-sm font-bold text-slate-800">ثبت سفارش پکیج VIP</div>--}}
            {{--                <div class="mt-1 text-xs text-slate-500">دیروز - ساعت ۱۸:۱۰</div>--}}
            {{--            </div>--}}
            {{--            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">--}}
            {{--                <div class="text-sm font-bold text-slate-800">ارسال تیکت پشتیبانی</div>--}}
            {{--                <div class="mt-1 text-xs text-slate-500">۳ روز پیش</div>--}}
            {{--            </div>--}}
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="text-base font-extrabold text-slate-900">میانبرهای سریع</div>
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
            <button @click="setTab('downloads')"
                    class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right hover:bg-white hover:shadow-sm transition">
                <div class="text-sm font-extrabold text-slate-900">رفتن به دانلودها</div>
                <div class="mt-1 text-xs text-slate-500">مشاهده فایل‌های خریداری شده</div>
            </button>
            <button @click="setTab('orders')"
                    class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right hover:bg-white hover:shadow-sm transition">
                <div class="text-sm font-extrabold text-slate-900">مدیریت سفارش‌ها</div>
                <div class="mt-1 text-xs text-slate-500">فاکتورها و جزئیات سفارش</div>
            </button>
            <button @click="setTab('tickets')"
                    class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right hover:bg-white hover:shadow-sm transition">
                <div class="text-sm font-extrabold text-slate-900">پشتیبانی</div>
                <div class="mt-1 text-xs text-slate-500">ثبت یا پیگیری تیکت</div>
            </button>
            <button @click="setTab('security')"
                    class="cursor-pointer rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right hover:bg-white hover:shadow-sm transition">
                <div class="text-sm font-extrabold text-slate-900">امنیت حساب</div>
                <div class="mt-1 text-xs text-slate-500">رمز عبور و تاییدیه</div>
            </button>
        </div>
    </div>
</section>
