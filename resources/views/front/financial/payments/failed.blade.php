@extends('front.master')

@php
    $rootItems = $order->rootItems ?? collect();

    $money = fn (int $amount): string => number_format($amount) . ' تومان';

    $failedAt = $payment->failed_at
        ? $payment->failed_at->format('Y/m/d H:i')
        : $payment->updated_at?->format('Y/m/d H:i');

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
        <div class="print-card w-full overflow-hidden rounded-xl2 border border-slate-200 bg-white shadow-soft2">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h1 class="text-base font-extrabold text-slate-900">رسید پرداخت سفارش</h1>
                    <p class="mt-1 text-xs text-slate-500">جزئیات پرداخت ناموفق و وضعیت سفارش</p>
                </div>

                <span
                    class="inline-flex w-fit items-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-[11px] font-extrabold text-red-700">
                    <span class="h-2 w-2 rounded-full bg-red-500"></span>
                    پرداخت ناموفق بود
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="min-w-0 p-5 sm:p-7 lg:col-span-8 lg:p-9">
                    <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                        <div
                            class="failed-pulse grid h-20 w-20 shrink-0 place-items-center rounded-3xl border border-red-200 bg-red-50 text-red-600">
                            <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.5"
                                      stroke-linecap="round"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-2xl font-black leading-tight text-slate-900 sm:text-3xl">
                                پرداخت شما انجام نشد
                            </h2>
                            <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600">
                                تراکنش شما تایید نشد و مبلغی برای این سفارش ثبت نشده است.
                                می‌توانید دوباره پرداخت را انجام دهید یا به سبد خرید برگردید.
                            </p>
                        </div>
                    </div>

                    <div class="mt-7 rounded-3xl border border-red-200 bg-red-50 p-4">
                        <div class="flex gap-3">
                            <div
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-red-700 ring-1 ring-red-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-sm font-extrabold text-red-900">دلیل احتمالی خطا</h3>
                                <p class="mt-1 text-xs leading-6 text-red-800">
                                    {{ $payment->gateway_message ?: 'پرداخت توسط بانک تایید نشده، ارتباط با درگاه قطع شده یا تراکنش توسط کاربر لغو شده است.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 min-w-0">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-extrabold text-slate-900">محصولات داخل سفارش</h3>
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
                                        alt="{{ data_get($item->snapshot, 'title', 'محصول سفارش') }}"
                                        class="h-20 w-24 shrink-0 rounded-2xl object-cover sm:h-24 sm:w-32"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="line-clamp-1 text-sm font-extrabold text-slate-900">
                                                {{ data_get($item->snapshot, 'title', 'آیتم سفارش') }}
                                            </h4>
                                            <span
                                                class="rounded-xl border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-extrabold text-amber-700">در انتظار پرداخت</span>
                                        </div>

                                        <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500">
                                            بعد از پرداخت موفق، دسترسی دانلود این محصول فعال می‌شود.
                                        </p>
                                    </div>

                                    <div class="hidden shrink-0 text-left sm:block">
                                        <div
                                            class="text-xs font-black text-slate-900">{{ $money((int) $item->total_amount) }}</div>
                                        <span
                                            class="mt-2 inline-flex text-xs font-extrabold text-slate-400">غیرفعال</span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        @if($order->status->canStartPayment())
                            <form method="POST"
                                  action="{{ route('payment.retry', ['payment' => $payment->tracking_code]) }}">
                                @csrf
                                <button type="submit"
                                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700 sm:w-auto">
                                    تلاش مجدد برای پرداخت
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('cart.index') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50">
                            بازگشت به سبد خرید
                            <span>←</span>
                        </a>

                        <a href="/"
                           class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50">
                            بازگشت به فروشگاه
                        </a>
                    </div>
                </div>

                <aside
                    class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0 lg:p-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                        <div>
                            <div class="text-xs font-bold text-slate-500">مبلغ پرداخت‌نشده</div>
                            <div
                                class="mt-2 text-xl font-black text-slate-900">{{ $money((int) $payment->amount) }}</div>
                        </div>

                        <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">شماره سفارش</span>
                                <span class="font-extrabold text-slate-900">{{ $order->order_number }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">کد پیگیری</span>
                                <span class="font-extrabold text-slate-900">{{ $payment->tracking_code }}</span>
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
                                <span class="text-slate-500">وضعیت پرداخت</span>
                                <span class="font-extrabold text-red-700">ناموفق</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">تاریخ تلاش</span>
                                <span class="font-extrabold text-slate-900">{{ $failedAt }}</span>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3">
                            <h3 class="text-xs font-extrabold text-red-900">سفارش هنوز پرداخت نشده است</h3>
                            <p class="mt-1 text-xs leading-6 text-red-800">
                                برای فعال شدن دانلودها، پرداخت را دوباره انجام دهید.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
