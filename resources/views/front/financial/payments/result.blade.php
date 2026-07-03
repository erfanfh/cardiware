@extends('front.master')

@php
    $rootItems = $order->rootItems ?? collect();

    $isPaid = $isPaid ?? $payment->isPaid();

    $isFreePayment = (int) $payment->amount === 0;

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
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300'
        : 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300';

    $statusDotClasses = $isPaid
        ? 'bg-emerald-500 dark:bg-emerald-400'
        : 'bg-red-500 dark:bg-red-400';

    $iconBoxClasses = $isPaid
        ? 'success-pulse border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300'
        : 'failed-pulse border-red-200 bg-red-50 text-red-600 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-300';

    $mainTitle = match (true) {
        $isFreePayment => 'سفارش رایگان با موفقیت ثبت شد',
        $isPaid => 'پرداخت با موفقیت انجام شد',
        default => 'پرداخت شما انجام نشد',
    };

    $mainDescription = match (true) {
        $isFreePayment => 'سفارش شما بدون نیاز به پرداخت تایید شد و دسترسی به محصولات فعال شده است. فایل‌ها از بخش دانلودهای حساب کاربری قابل دریافت هستند.',
        $isPaid => 'سفارش شما تایید شد و دسترسی به محصولات خریداری‌شده فعال شده است. فایل‌ها از بخش دانلودهای حساب کاربری قابل دریافت هستند.',
        default => 'تراکنش شما تایید نشد و مبلغی برای این سفارش ثبت نشده است. می‌توانید دوباره پرداخت را انجام دهید یا به سبد خرید برگردید.',
    };

    $paymentStatusLabel = match (true) {
        $isFreePayment => 'سفارش رایگان تایید شد',
        $isPaid => 'پرداخت تایید شد',
        default => 'پرداخت ناموفق بود',
    };

    $itemsTitle = $isPaid ? 'محصولات خریداری‌شده' : 'محصولات داخل سفارش';

    $amountTitle = match (true) {
        $isFreePayment => 'مبلغ قابل پرداخت',
        $isPaid => 'مبلغ پرداخت‌شده',
        default => 'مبلغ پرداخت‌نشده',
    };

    $dateTitle = match (true) {
        $isFreePayment => 'تاریخ ثبت سفارش',
        $isPaid => 'تاریخ پرداخت',
        default => 'تاریخ تلاش',
    };

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
    <section class="mx-auto flex max-w-7xl items-center justify-center px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">
        <div
            class="print-card w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-soft2 dark:border-slate-700 dark:bg-slate-900">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7 dark:border-slate-700 dark:bg-slate-900"
            >
                <div class="min-w-0">
                    <h1 class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-slate-100">
                        رسید پرداخت سفارش
                    </h1>

                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        {{ $isPaid ? 'جزئیات پرداخت موفق و محصولات خریداری‌شده' : 'جزئیات پرداخت ناموفق و وضعیت سفارش' }}
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center gap-2 rounded-2xl border px-3 py-2 text-[11px] font-extrabold {{ $statusBadgeClasses }}"
                >
        <span class="h-2 w-2 rounded-full {{ $statusDotClasses }}"></span>
        {{ $paymentStatusLabel }}
    </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="min-w-0 p-4 sm:p-7 lg:col-span-8 lg:p-9">
                    <div
                        class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-center sm:gap-5 sm:text-right"
                    >
                        <div
                            class="grid h-16 w-16 shrink-0 place-items-center rounded-3xl border sm:h-20 sm:w-20 {{ $iconBoxClasses }}
        dark:border-slate-700"
                        >
                            @if($isPaid)
                                <svg class="h-9 w-9 sm:h-11 sm:w-11" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 6L9 17l-5-5"
                                          stroke="currentColor"
                                          stroke-width="2.5"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg class="h-9 w-9 sm:h-11 sm:w-11" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 6L6 18M6 6l12 12"
                                          stroke="currentColor"
                                          stroke-width="2.5"
                                          stroke-linecap="round"/>
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h2 class="text-xl font-black leading-8 text-slate-900 sm:text-3xl sm:leading-tight dark:text-slate-100">
                                {{ $mainTitle }}
                            </h2>

                            <p class="mx-auto mt-3 max-w-xl text-sm leading-7 text-slate-600 sm:mx-0 dark:text-slate-300">
                                {{ $mainDescription }}
                            </p>
                        </div>
                    </div>

                    @unless($isPaid)
                        <div
                            class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 sm:mt-7 sm:rounded-3xl dark:border-red-900/40 dark:bg-red-900/20">
                            <div class="flex gap-3">
                                <div
                                    class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-white text-red-700 ring-1 ring-red-200 dark:bg-slate-900 dark:text-red-400 dark:ring-red-900/40"
                                >
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M12 9v4m0 4h.01M10.3 4.6 2.8 17.5A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.5L13.7 4.6a2 2 0 0 0-3.4 0Z"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <h3 class="text-sm font-extrabold text-red-900 dark:text-red-300">دلیل احتمالی
                                        خطا</h3>
                                    <p class="mt-1 text-xs leading-6 text-red-800 dark:text-red-400">
                                        {{ $payment->gateway_message ?: 'پرداخت توسط بانک تایید نشده، ارتباط با درگاه قطع شده یا تراکنش توسط کاربر لغو شده است.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endunless

                    <div class="mt-5 min-w-0 sm:mt-7">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">{{ $itemsTitle }}</h3>

                            <span
                                class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
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
                                    class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-soft sm:flex-row sm:items-center sm:rounded-3xl dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <x-front.responsive-image
                                        :media="$item->purchasable?->product?->picture"
                                        :fallback="$itemImage($item)"
                                        :alt="data_get($item->snapshot, 'title', 'محصول سفارش')"
                                        :title="data_get($item->snapshot, 'title', 'محصول سفارش')"
                                        variant="thumbnail"
                                        class="aspect-video w-full rounded-2xl object-cover sm:h-24 sm:w-32 sm:shrink-0"
                                        wrapper-class="aspect-video w-full rounded-2xl sm:h-24 sm:w-32 sm:shrink-0"
                                        :skeleton="true"
                                        loading="lazy"
                                        decoding="async"
                                        sizes="(max-width: 640px) 100vw, 128px"
                                    />

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="line-clamp-2 text-sm font-extrabold leading-7 text-slate-900 sm:line-clamp-1 dark:text-slate-100">
                                                {{ data_get($item->snapshot, 'title', 'آیتم سفارش') }}
                                            </h4>

                                            @if($isPaid)
                                                <span
                                                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                                فعال
                            </span>
                                            @else
                                                <span
                                                    class="rounded-xl border border-amber-200 bg-amber-50 px-2 py-1 text-[10px] font-extrabold text-amber-700 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300">
                                در انتظار پرداخت
                            </span>
                                            @endif
                                        </div>

                                        <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                            @if($isPaid)
                                                فایل محصول و راهنمای نصب برای دانلود آماده است.
                                            @else
                                                بعد از پرداخت موفق، دسترسی دانلود این محصول فعال می‌شود.
                                            @endif
                                        </p>

                                        @if($item->children->isNotEmpty())
                                            <div
                                                class="mt-3 space-y-2 rounded-2xl border border-brand-100 bg-brand-50/60 p-2.5 dark:border-slate-700 dark:bg-slate-800/40"
                                            >
                                                <div
                                                    class="text-[11px] font-extrabold text-brand-800 dark:text-brand-300">
                                                    خدمات افزوده خریداری‌شده
                                                </div>

                                                <div class="space-y-1.5">
                                                    @foreach($item->children as $child)
                                                        <div
                                                            class="flex items-center justify-between gap-3 text-[11px]">
                                        <span class="line-clamp-1 font-bold text-brand-800 dark:text-brand-300">
                                            {{ data_get($child->snapshot, 'title', 'خدمت افزوده') }}
                                        </span>

                                                            <span
                                                                class="shrink-0 font-extrabold text-brand-900 dark:text-brand-200">
                                            {{ $money((int) $child->total_amount) }}
                                        </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <div
                                            class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-2.5 text-[11px] sm:hidden dark:border-slate-700 dark:bg-slate-800">
                                            <div class="flex items-center justify-between gap-3">
                                                <span class="text-slate-500 dark:text-slate-400">قیمت محصول</span>
                                                <span class="font-extrabold text-slate-900 dark:text-slate-100">
                                {{ $money((int) $item->total_amount) }}
                            </span>
                                            </div>

                                            @if($childrenTotal > 0)
                                                <div class="mt-1.5 flex items-center justify-between gap-3">
                                                    <span class="text-slate-500 dark:text-slate-400">خدمات افزوده</span>
                                                    <span class="font-extrabold text-brand-700 dark:text-brand-300">
                                    {{ $money((int) $childrenTotal) }}
                                </span>
                                                </div>

                                                <div
                                                    class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-2 dark:border-slate-700">
                                                    <span class="font-extrabold text-slate-700 dark:text-slate-300">جمع این محصول</span>
                                                    <span class="font-black text-slate-900 dark:text-slate-100">
                                    {{ $money($itemGrandTotal) }}
                                </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="hidden shrink-0 text-left sm:block">
                                        <div class="space-y-1 text-xs">
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="text-slate-500 dark:text-slate-400">محصول:</span>
                                                <span class="font-black text-slate-900 dark:text-slate-100">
                                {{ $money((int) $item->total_amount) }}
                            </span>
                                            </div>

                                            @if($childrenTotal > 0)
                                                <div class="flex items-center justify-end gap-2">
                                                    <span
                                                        class="text-slate-500 dark:text-slate-400">خدمات افزوده:</span>
                                                    <span class="font-black text-brand-700 dark:text-brand-300">
                                    {{ $money((int) $childrenTotal) }}
                                </span>
                                                </div>

                                                <div class="mt-1 border-t border-slate-100 pt-1 dark:border-slate-700">
                                <span class="font-black text-slate-900 dark:text-slate-100">
                                    {{ $money($itemGrandTotal) }}
                                </span>
                                                </div>
                                            @endif
                                        </div>

                                        @if($isPaid)
                                            <a href="{{ $downloadsUrl }}"
                                               class="mt-2 inline-flex text-xs font-extrabold text-brand-700 transition hover:text-brand-800 dark:text-brand-300 dark:hover:text-brand-200">
                                                دانلود ←
                                            </a>
                                        @else
                                            <span
                                                class="mt-2 inline-flex text-xs font-extrabold text-slate-400 dark:text-slate-500">
                            غیرفعال
                        </span>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div
                                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5 text-center text-sm font-bold text-slate-500 sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                    آیتمی برای این سفارش یافت نشد.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3 sm:mt-7 sm:flex sm:flex-row sm:flex-wrap">
                        @if($isPaid)
                            <a href="{{ $downloadsUrl }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700">
                                مشاهده دانلودها
                            </a>

                            <a href="{{ $productsUrl }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800">
                                بازگشت به فروشگاه
                                <span>←</span>
                            </a>
                        @else
                            <a href="{{ route('cart.index') }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50 dark:border-slate-700 dark:bg-slate-900 dark:text-brand-300 dark:hover:bg-slate-800">
                                بازگشت به سبد خرید
                                <span>←</span>
                            </a>

                            <a href="{{ $productsUrl }}"
                               class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-800 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                بازگشت به فروشگاه
                            </a>
                        @endif
                    </div>
                </div>

                <aside
                    class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0 lg:p-5
    dark:border-slate-700 dark:bg-slate-900"
                >
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-4 sm:rounded-3xl dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div
                                    class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ $amountTitle }}</div>
                                <div class="mt-2 text-lg font-black text-slate-900 sm:text-xl dark:text-slate-100">
                                    {{ $money((int) $payment->amount) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-4 dark:border-slate-700">
                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">شماره سفارش</span>
                                <span
                                    class="text-left font-extrabold text-slate-900 dark:text-slate-100">{{ $order->order_number }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">کد رسید</span>
                                <span
                                    class="break-all text-left font-extrabold text-slate-900 dark:text-slate-100">{{ $receiptCode }}</span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">جمع محصولات</span>
                                <span class="text-left font-extrabold text-slate-900 dark:text-slate-100">
                    {{ $money((int) $order->subtotal_amount) }}
                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">تخفیف</span>
                                <span class="text-left font-extrabold text-emerald-700 dark:text-emerald-400">
                    {{ $money((int) $order->discount_amount) }}
                </span>
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">وضعیت پرداخت</span>

                                @if($isPaid)
                                    <span class="font-extrabold text-emerald-700 dark:text-emerald-400">موفق</span>
                                @else
                                    <span class="font-extrabold text-red-700 dark:text-red-400">ناموفق</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between gap-3 text-xs">
                                <span class="text-slate-500 dark:text-slate-400">{{ $dateTitle }}</span>
                                <span class="text-left font-extrabold text-slate-900 dark:text-slate-100">
                    {{ jalali_date($happenedAt) }}
                </span>
                            </div>
                        </div>

                        @if($isPaid)
                            <div
                                class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900/40 dark:bg-emerald-900/20">
                                <div class="flex gap-3">
                                    <div>
                                        <h3 class="text-xs font-extrabold text-emerald-900 dark:text-emerald-300">پرداخت
                                            امن و تاییدشده</h3>
                                        <p class="mt-1 text-xs leading-6 text-emerald-800 dark:text-emerald-400">
                                            اطلاعات تراکنش در حساب کاربری شما ذخیره شده است.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div
                                class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-3 dark:border-red-900/40 dark:bg-red-900/20">
                                <h3 class="text-xs font-extrabold text-red-900 dark:text-red-300">سفارش هنوز پرداخت نشده
                                    است</h3>
                                <p class="mt-1 text-xs leading-6 text-red-800 dark:text-red-400">
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
