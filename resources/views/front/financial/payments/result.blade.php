@extends('front.master')

@php
    $rootItems = $order->rootItems ?? collect();

    $isPaid = $isPaid ?? $payment->isPaid();

    $money = fn (int $amount): string => number_format($amount) . ' تومان';

    $happenedAt = $isPaid
        ? ($payment->paid_at?->format('Y/m/d H:i') ?? now()->format('Y/m/d H:i'))
        : ($payment->failed_at?->format('Y/m/d H:i') ?? $payment->updated_at?->format('Y/m/d H:i') ?? now()->format('Y/m/d H:i'));

    $itemImage = function ($item) {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return data_get($item->snapshot, 'image_url')
            ?? $purchasable?->image_url
            ?? $product?->picture?->url
            ?? asset('front/assets/images/placeholder.webp');
    };

    $statusBadgeClasses = $isPaid
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-red-200 bg-red-50 text-red-700';

    $statusDotClasses = $isPaid ? 'bg-emerald-500' : 'bg-red-500';

    $iconBoxClasses = $isPaid
        ? 'success-pulse border-emerald-200 bg-emerald-50 text-emerald-600'
        : 'failed-pulse border-red-200 bg-red-50 text-red-600';

    $mainTitle = $isPaid ? 'پرداخت با موفقیت انجام شد' : 'پرداخت شما انجام نشد';

    $mainDescription = $isPaid
        ? 'سفارش شما تایید شد و دسترسی به محصولات خریداری‌شده فعال شده است. فایل‌ها از بخش دانلودهای حساب کاربری قابل دریافت هستند.'
        : 'تراکنش شما تایید نشد و مبلغی برای این سفارش ثبت نشده است. می‌توانید دوباره پرداخت را انجام دهید یا به سبد خرید برگردید.';

    $paymentStatusLabel = $isPaid ? 'پرداخت تایید شد' : 'پرداخت ناموفق بود';

    $itemsTitle = $isPaid ? 'محصولات خریداری‌شده' : 'محصولات داخل سفارش';

    $amountTitle = $isPaid ? 'مبلغ پرداخت‌شده' : 'مبلغ پرداخت‌نشده';

    $dateTitle = $isPaid ? 'تاریخ پرداخت' : 'تاریخ تلاش';

    $receiptCode = $payment->gateway_transaction_id
        ?? $payment->gateway_reference
        ?? $payment->tracking_code;

    $downloadsUrl = route('account.downloads');

    $productsUrl = \Illuminate\Support\Facades\Route::has('products.list')
        ? route('products.list')
        : url('/');
@endphp

@section('head')
    <title>{{ $mainTitle . ' | ' . get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section class="mx-auto flex max-w-7xl items-center justify-center px-4 py-8">
        <div class="print-card w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft2">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                <div>
                    <h1 class="text-base font-extrabold text-slate-900">رسید پرداخت سفارش</h1>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $isPaid ? 'جزئیات پرداخت موفق و محصولات خریداری‌شده' : 'جزئیات پرداخت ناموفق و وضعیت سفارش' }}
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center gap-2 rounded-2xl border px-3 py-2 text-[11px] font-extrabold {{ $statusBadgeClasses }}">
                    <span class="h-2 w-2 rounded-full {{ $statusDotClasses }}"></span>
                    {{ $paymentStatusLabel }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="min-w-0 p-5 sm:p-7 lg:col-span-8 lg:p-9">
                    <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                        <div
                            class="grid h-20 w-20 shrink-0 place-items-center rounded-3xl border {{ $iconBoxClasses }}">
                            @if($isPaid)
                                <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 6L9 17l-5-5"
                                          stroke="currentColor"
                                          stroke-width="2.5"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg class="h-11 w-11" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 6L6 18M6 6l12 12"
                                          stroke="currentColor"
                                          stroke-width="2.5"
                                          stroke-linecap="round"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <h2 class="text-2xl font-black leading-tight text-slate-900 sm:text-3xl">
                                {{ $mainTitle }}
                            </h2>
                            <p class="mt-3 max-w-xl text-sm leading-7 text-slate-600">
                                {{ $mainDescription }}
                            </p>
                        </div>
                    </div>

                    @unless($isPaid)
                        <div class="mt-7 rounded-3xl border border-red-200 bg-red-50 p-4">
                            <div class="flex gap-3">
                                <div
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-red-700 ring-1 ring-red-200">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
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
                    @endunless

                    <div class="mt-7 min-w-0">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-extrabold text-slate-900">{{ $itemsTitle }}</h3>
                            <span
                                class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                                {{ $rootItems->count() }} محصول
                            </span>
                        </div>

                        <div class="space-y-3">
                            @forelse($rootItems as $item)
                                @php
                                    $childrenTotal = $item->children->sum(fn ($child) => (int) $child->total_amount);
                                    $itemGrandTotal = (int) $item->total_amount + $childrenTotal;
                                @endphp

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

                                            @if($isPaid)
                                                <span
                                                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700">
                                                    فعال
                                                </span>
                                            @else
                                                <span
                                                    class="rounded-xl border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-extrabold text-amber-700">
                                                    در انتظار پرداخت
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500">
                                            @if($isPaid)
                                                فایل محصول و راهنمای نصب برای دانلود آماده است.
                                            @else
                                                بعد از پرداخت موفق، دسترسی دانلود این محصول فعال می‌شود.
                                            @endif
                                        </p>

                                        @if($item->children->isNotEmpty())
                                            <div
                                                class="mt-3 space-y-2 rounded-2xl border border-brand-100 bg-brand-50/60 p-2.5">
                                                <div class="text-[11px] font-extrabold text-brand-800">
                                                    خدمات افزوده خریداری‌شده
                                                </div>

                                                <div class="space-y-1.5">
                                                    @foreach($item->children as $child)
                                                        <div
                                                            class="flex items-center justify-between gap-3 text-[11px]">
                                                            <span class="line-clamp-1 font-bold text-brand-800">
                                                                {{ data_get($child->snapshot, 'title', 'خدمت افزوده') }}
                                                            </span>

                                                            <span class="shrink-0 font-extrabold text-brand-900">
                                                                {{ $money((int) $child->total_amount) }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-2.5 text-[11px] sm:hidden">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-slate-500">قیمت محصول</span>
                                                <span class="font-extrabold text-slate-900">
                                                    {{ $money((int) $item->total_amount) }}
                                                </span>
                                            </div>

                                            @if($childrenTotal > 0)
                                                <div class="mt-1.5 flex items-center justify-between gap-3">
                                                    <span class="text-slate-500">خدمات افزوده</span>
                                                    <span class="font-extrabold text-brand-700">
                                                        {{ $money((int) $childrenTotal) }}
                                                    </span>
                                                </div>

                                                <div
                                                    class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-2">
                                                    <span class="font-extrabold text-slate-700">جمع این محصول</span>
                                                    <span class="font-black text-slate-900">
                                                        {{ $money($itemGrandTotal) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="hidden shrink-0 text-left sm:block">
                                        <div class="space-y-1 text-xs">
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="text-slate-500">محصول:</span>
                                                <span class="font-black text-slate-900">
                                                    {{ $money((int) $item->total_amount) }}
                                                </span>
                                            </div>

                                            @if($childrenTotal > 0)
                                                <div class="flex items-center justify-end gap-2">
                                                    <span class="text-slate-500">خدمات افزوده:</span>
                                                    <span class="font-black text-brand-700">
                                                        {{ $money((int) $childrenTotal) }}
                                                    </span>
                                                </div>

                                                <div class="mt-1 border-t border-slate-100 pt-1">
                                                    <span class="font-black text-slate-900">
                                                        {{ $money($itemGrandTotal) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($isPaid)
                                            <a href="{{ $downloadsUrl }}"
                                               class="mt-2 inline-flex text-xs font-extrabold text-brand-700 transition hover:text-brand-800">
                                                دانلود ←
                                            </a>
                                        @else
                                            <span class="mt-2 inline-flex text-xs font-extrabold text-slate-400">
                                                غیرفعال
                                            </span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div
                                    class="rounded-3xl border border-slate-200 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500">
                                    آیتمی برای این سفارش یافت نشد.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                        @if($isPaid)
                            <a href="{{ $downloadsUrl }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700">
                                مشاهده دانلودها
                            </a>

                            <a href="{{ $productsUrl }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50">
                                بازگشت به فروشگاه
                                <span>←</span>
                            </a>
                        @else
                            @if(method_exists($order->status, 'canStartPayment') && $order->status->canStartPayment())
                                <form method="POST"
                                      action="{{ route('payment.retry', ['paymentToken' => $payment->public_token]) }}">
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

                            <a href="{{ $productsUrl }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50">
                                بازگشت به فروشگاه
                            </a>
                        @endif
                    </div>
                </div>

                <aside
                    class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0 lg:p-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-xs font-bold text-slate-500">{{ $amountTitle }}</div>
                                <div class="mt-2 text-xl font-black text-slate-900">
                                    {{ $money((int) $payment->amount) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-4">
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">شماره سفارش</span>
                                <span class="font-extrabold text-slate-900">{{ $order->order_number }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">کد رسید</span>
                                <span class="font-extrabold text-slate-900">{{ $receiptCode }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">جمع محصولات</span>
                                <span class="font-extrabold text-slate-900">
                                    {{ $money((int) $order->subtotal_amount) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">تخفیف</span>
                                <span class="font-extrabold text-emerald-700">
                                    {{ $money((int) $order->discount_amount) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">وضعیت پرداخت</span>

                                @if($isPaid)
                                    <span class="font-extrabold text-emerald-700">موفق</span>
                                @else
                                    <span class="font-extrabold text-red-700">ناموفق</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500">{{ $dateTitle }}</span>
                                <span class="font-extrabold text-slate-900">{{ jalali_date($happenedAt) }}</span>
                            </div>
                        </div>

                        @if($isPaid)
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
                        @else
                            <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3">
                                <h3 class="text-xs font-extrabold text-red-900">سفارش هنوز پرداخت نشده است</h3>
                                <p class="mt-1 text-xs leading-6 text-red-800">
                                    برای فعال شدن دانلودها، پرداخت را دوباره انجام دهید.
                                </p>
                            </div>
                        @endif
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
