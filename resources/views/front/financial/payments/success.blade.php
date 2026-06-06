@extends('front.master')

@php
    $rootItems = $order->rootItems ?? collect();

    $money = fn (int $amount): string => number_format($amount) . ' تومان';

    $paidAt = $payment->paid_at
        ? $payment->paid_at->format('Y/m/d H:i')
        : now()->format('Y/m/d H:i');

    $itemImage = function ($item) {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return data_get($item->snapshot, 'image_url')
            ?? $purchasable?->image_url
            ?? $product?->picture?->url
            ?? asset('front/assets/images/placeholder.webp');
    };
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-8 flex items-center justify-center">
        <div class="print-card w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft2">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h1 class="text-base font-extrabold text-slate-900">رسید پرداخت سفارش</h1>
                    <p class="mt-1 text-xs text-slate-500">جزئیات پرداخت موفق و محصولات خریداری‌شده</p>
                </div>

                <span
                    class="inline-flex w-fit items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-extrabold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    پرداخت تایید شد
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="min-w-0 p-5 sm:p-7 lg:col-span-8 lg:p-9">
                    <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                        <div
                            class="success-pulse grid h-20 w-20 shrink-0 place-items-center rounded-3xl border border-emerald-200 bg-emerald-50 text-emerald-600">
                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none">
                                <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-2xl font-black leading-tight text-slate-900 sm:text-3xl">
                                پرداخت با موفقیت انجام شد
                            </h2>
                            <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600">
                                سفارش شما تایید شد و دسترسی به محصولات خریداری‌شده فعال شده است.
                                فایل‌ها از بخش دانلودهای حساب کاربری قابل دریافت هستند.
                            </p>
                        </div>
                    </div>

                    <div class="mt-7 min-w-0">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-extrabold text-slate-900">محصولات خریداری‌شده</h3>
                            <span
                                class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                                {{ $rootItems->count() }} محصول
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach($rootItems as $item)
                                <article
                                    class="flex items-center gap-3 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-soft">
                                    <img
                                        src="{{ $itemImage($item) }}"
                                        alt="{{ data_get($item->snapshot, 'title', 'محصول خریداری‌شده') }}"
                                        class="h-20 w-24 shrink-0 rounded-2xl object-cover sm:h-24 sm:w-32"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="line-clamp-1 text-sm font-extrabold text-slate-900">
                                                {{ data_get($item->snapshot, 'title', 'آیتم سفارش') }}
                                            </h4>
                                            <span
                                                class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700">فعال</span>
                                        </div>

                                        <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500">
                                            فایل محصول و راهنمای نصب برای دانلود آماده است.
                                        </p>

                                        @if($item->children->isNotEmpty())
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @foreach($item->children as $child)
                                                    <span
                                                        class="rounded-xl border border-brand-100 bg-brand-50 px-2 py-1 text-[10px] font-extrabold text-brand-700">
                                                        {{ data_get($child->snapshot, 'title', 'خدمت افزوده') }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="hidden shrink-0 text-left sm:block">
                                        <div
                                            class="text-xs font-black text-slate-900">{{ $money((int) $item->total_amount) }}</div>
                                        <a href="#"
                                           class="mt-2 inline-flex text-xs font-extrabold text-brand-700 transition hover:text-brand-800">دانلود
                                            ←</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        <a href="#"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700">
                            مشاهده دانلودها
                        </a>

                        <a href="{{ route('cart.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50">
                            بازگشت به فروشگاه
                            <span>←</span>
                        </a>

                        <button onclick="window.print()" type="button"
                                class="no-print inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50">
                            چاپ رسید
                        </button>
                    </div>
                </div>

                <aside
                    class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0 lg:p-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-slate-500">مبلغ پرداخت‌شده</div>
                                <div
                                    class="mt-2 text-xl font-black text-slate-900">{{ $money((int) $payment->amount) }}</div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">شماره سفارش</span>
                                <span class="font-extrabold text-slate-900">{{ $order->order_number }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">کد پیگیری</span>
                                <span
                                    class="font-extrabold text-slate-900">{{ $payment->gateway_transaction_id ?? $payment->tracking_code }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">جمع محصولات</span>
                                <span
                                    class="font-extrabold text-slate-900">{{ $money((int) $order->subtotal_amount) }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">تخفیف</span>
                                <span
                                    class="font-extrabold text-emerald-700">{{ $money((int) $order->discount_amount) }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">تاریخ پرداخت</span>
                                <span class="font-extrabold text-slate-900">{{ $paidAt }}</span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3">
                            <div class="flex gap-3">
                                <div>
                                    <h3 class="text-xs font-extrabold text-emerald-900">پرداخت امن و تاییدشده</h3>
                                    <p class="mt-1 text-xs leading-6 text-emerald-800">
                                        اطلاعات تراکنش در حساب کاربری شما ذخیره شده است.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
