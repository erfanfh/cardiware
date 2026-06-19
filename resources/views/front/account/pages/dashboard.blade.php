@extends('front.account.layout')

@section('page-content')
    <div class="flex min-w-0 flex-col gap-4 sm:gap-6">
        <section
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-6 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
            <a
                href="{{ route('account.downloads') }}"
                class="group relative block overflow-hidden rounded-2xl border border-emerald-100 bg-white p-4 text-right shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-soft sm:rounded-3xl sm:p-5"
            >
                <div
                    class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full bg-emerald-100/70 blur-2xl transition group-hover:bg-emerald-200/80 sm:h-24 sm:w-24"></div>

                <div class="relative flex items-start justify-between gap-3">
                    <div
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-600 sm:h-11 sm:w-11">
                        @include('icons.cloud-download')
                    </div>

                    <span
                        class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-extrabold text-emerald-700 sm:text-[11px]">
                        آماده دریافت
                    </span>
                </div>

                <div class="relative mt-4 sm:mt-5">
                    <div class="text-sm font-extrabold text-slate-900">دانلودهای آماده</div>
                    <div class="mt-1 text-xs leading-5 text-slate-500">فایل‌های قابل دریافت</div>

                    <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5">
                        <div class="text-2xl font-black text-slate-900 sm:text-3xl">
                            {{ $user->purchase_accesses_count }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-xs font-extrabold text-emerald-600 transition [&_svg]:!h-[14px] [&_svg]:!w-[14px]">
                            مشاهده
                            @include('icons.arrow-left-02')
                        </div>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('account.orders') }}"
                class="group relative block overflow-hidden rounded-2xl border border-brand-100 bg-white p-4 text-right shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-soft sm:rounded-3xl sm:p-5"
            >
                <div
                    class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full bg-brand-100/70 blur-2xl transition group-hover:bg-brand-200/80 sm:h-24 sm:w-24"></div>

                <div class="relative flex items-start justify-between gap-3">
                    <div
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-600 sm:h-11 sm:w-11">
                        @include('icons.shopping-basket-03')
                    </div>

                    <span
                        class="rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-extrabold text-brand-700 sm:text-[11px]">
                        پرداخت موفق
                    </span>
                </div>

                <div class="relative mt-4 sm:mt-5">
                    <div class="text-sm font-extrabold text-slate-900">سفارش‌های تکمیل شده</div>
                    <div class="mt-1 text-xs leading-5 text-slate-500">سفارش‌های موفق فروشگاه</div>

                    <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5">
                        <div class="text-2xl font-black text-slate-900 sm:text-3xl">
                            {{ $user->paid_orders_count }}
                        </div>

                        <div
                            class="flex items-center gap-1 text-xs font-extrabold text-brand-600 transition [&_svg]:!h-[14px] [&_svg]:!w-[14px]">
                            مشاهده
                            @include('icons.arrow-left-02')
                        </div>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('account.tickets') }}"
                class="group relative block overflow-hidden rounded-2xl border border-cyan-100 bg-white p-4 text-right shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-soft sm:rounded-3xl sm:p-5"
            >
                <div
                    class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full bg-cyan-100/70 blur-2xl transition group-hover:bg-cyan-200/80 sm:h-24 sm:w-24"></div>

                <div class="relative flex items-start justify-between gap-3">
                    <div
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-cyan-100 bg-cyan-50 text-cyan-600 sm:h-11 sm:w-11">
                        @include('icons.message-search-02')
                    </div>

                    <span
                        class="rounded-full bg-cyan-50 px-2.5 py-1 text-[10px] font-extrabold text-cyan-700 sm:text-[11px]">
                        نیازمند پیگیری
                    </span>
                </div>

                <div class="relative mt-4 sm:mt-5">
                    <div class="text-sm font-extrabold text-slate-900">تیکت‌های فعال</div>
                    <div class="mt-1 text-xs leading-5 text-slate-500">در انتظار پاسخ یا پیگیری</div>

                    <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5">
                        <div class="text-2xl font-black text-slate-900 sm:text-3xl">0</div>

                        <div
                            class="flex items-center gap-1 text-xs font-extrabold text-cyan-600 transition [&_svg]:!h-[14px] [&_svg]:!w-[14px]">
                            پیگیری
                            @include('icons.arrow-left-02')
                        </div>
                    </div>
                </div>
            </a>

            <a
                href="{{ route('account.favorites') }}"
                class="group relative block overflow-hidden rounded-2xl border border-rose-100 bg-white p-4 text-right shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-soft sm:rounded-3xl sm:p-5"
            >
                <div
                    class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full bg-rose-100/70 blur-2xl transition group-hover:bg-rose-200/80 sm:h-24 sm:w-24"></div>

                <div class="relative flex items-start justify-between gap-3">
                    <div
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-rose-100 bg-rose-50 text-rose-600 sm:h-11 sm:w-11">
                        @include('icons.favourite')
                    </div>

                    <span
                        class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-extrabold text-rose-700 sm:text-[11px]">
                        ذخیره‌شده
                    </span>
                </div>

                <div class="relative mt-4 sm:mt-5">
                    <div class="text-sm font-extrabold text-slate-900">علاقه‌مندی‌ها</div>
                    <div class="mt-1 text-xs leading-5 text-slate-500">محصولات ذخیره‌شده برای بعد</div>

                    <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5">
                        <div class="text-2xl font-black text-slate-900 sm:text-3xl">0</div>

                        <div
                            class="flex items-center gap-1 text-xs font-extrabold text-rose-600 transition [&_svg]:!h-[14px] [&_svg]:!w-[14px]">
                            مشاهده
                            @include('icons.arrow-left-02')
                        </div>
                    </div>
                </div>
            </a>
        </section>

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-6">
            <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-slate-900 sm:text-base">
                            پربازدیدهای دیروز
                        </div>

                        <div class="mt-1 text-xs leading-5 text-slate-500">
                            نوشته‌هایی که دیروز بیشتر دیده شدند
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-3 sm:mt-5">
                    @forelse($yesterdayTopPosts as $view)
                        @php
                            $post = $view->pageable;
                        @endphp

                        @if($post)
                            <a
                                href="{{ route('contents.detail', $post->slug) }}"
                                class="group block rounded-2xl border border-slate-200 bg-slate-50 p-3 transition hover:bg-white hover:shadow-sm sm:p-4"
                            >
                                <div class="flex items-start justify-between gap-3 sm:gap-4">
                                    <div class="min-w-0">
                                        <div
                                            class="line-clamp-1 text-sm font-extrabold text-slate-900 transition group-hover:text-brand-700">
                                            {{ $post->title }}
                                        </div>

                                        <div class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">
                                            {{ $post->excerpt ?? 'یکی از نوشته‌های پربازدید مودینگ‌وی در روز گذشته' }}
                                        </div>
                                    </div>

                                    <div
                                        class="shrink-0 rounded-2xl border border-slate-200 bg-white px-2.5 py-2 text-center sm:px-3">
                                        <div class="text-xs font-black text-slate-900 sm:text-sm">
                                            {{ number_format($view->count) }}
                                        </div>

                                        <div class="mt-0.5 text-[10px] font-bold text-slate-400">
                                            بازدید
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endif
                    @empty
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center text-xs text-slate-500 sm:rounded-3xl">
                            نوشته‌ای یافت نشد!
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <div class="text-sm font-extrabold text-slate-900 sm:text-base">
                    میانبرهای سریع
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-5 sm:grid-cols-2">
                    <a
                        href="{{ route('account.downloads') }}"
                        class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right transition hover:bg-white hover:shadow-sm"
                    >
                        <div class="text-sm font-extrabold text-slate-900">رفتن به دانلودها</div>
                        <div class="mt-1 text-xs leading-5 text-slate-500">مشاهده فایل‌های خریداری شده</div>
                    </a>

                    <a
                        href="{{ route('account.orders') }}"
                        class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right transition hover:bg-white hover:shadow-sm"
                    >
                        <div class="text-sm font-extrabold text-slate-900">مدیریت سفارش‌ها</div>
                        <div class="mt-1 text-xs leading-5 text-slate-500">فاکتورها و جزئیات سفارش</div>
                    </a>

                    <a
                        href="{{ route('account.tickets') }}"
                        class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right transition hover:bg-white hover:shadow-sm"
                    >
                        <div class="text-sm font-extrabold text-slate-900">پشتیبانی</div>
                        <div class="mt-1 text-xs leading-5 text-slate-500">ثبت یا پیگیری تیکت</div>
                    </a>

                    <a
                        href="{{ route('account.security') }}"
                        class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 text-right transition hover:bg-white hover:shadow-sm"
                    >
                        <div class="text-sm font-extrabold text-slate-900">امنیت حساب</div>
                        <div class="mt-1 text-xs leading-5 text-slate-500">رمز عبور و تاییدیه</div>
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
