@extends('admin.layout')

@section('title', 'ویرایش سفارش ' . $order->order_number)

@php
    use App\Domain\Catalog\Models\ProductSaleItem;
    use Illuminate\Support\Facades\Route;

    $money = fn (?int $amount): string => number_format((int) $amount) . ' تومان';

    $rootItems = $order->rootItems ?? collect();
    $user = $order->user;
    $latestPayment = $order->latestPayment;
    $refund = $order->refund;

    $subtotalAmount = (int) ($order->subtotal_amount ?? 0);
    $discountAmount = (int) ($order->discount_amount ?? 0);
    $taxAmount = (int) ($order->tax_amount ?? 0);
    $taxRate = rtrim(rtrim(number_format(((int) ($order->tax_rate_basis_points ?? 0)) / 100, 2, '.', ''), '0'), '.');
    $totalAmount = (int) ($order->total_amount ?? max(0, $subtotalAmount - $discountAmount));

    $status = $order->status;
    $statusValue = $status->value;
    $statusLabel = $status->label();

    $statusClasses = match ($statusValue) {
        'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
        'pending_payment' => 'border-amber-200 bg-amber-50 text-amber-700',
        'failed' => 'border-red-200 bg-red-50 text-red-700',
        'cancelled' => 'border-slate-200 bg-slate-100 text-slate-700',
        'refunded' => 'border-sky-200 bg-sky-50 text-sky-700',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };

    $gateway = $latestPayment?->gateway?->label() ?? '—';
    $paymentStatus = $latestPayment?->status?->label() ?? '—';
    $paymentTrackingCode = $latestPayment?->tracking_code ?? '—';

    $orderDate = $order->created_at ? jalali_date($order->created_at) : '—';
    $paidAt = $order->paid_at ? jalali_date($order->paid_at) : '—';
@endphp

@section('content')
    <x-admin.page>
        <div class="space-y-6">
            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Header --}}
            <div class="overflow-hidden bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/80 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-xl font-black text-slate-900">
                                سفارش {{ $order->order_number }}
                            </h1>

                            <span
                                class="inline-flex items-center rounded-2xl border px-3 py-1.5 text-xs font-extrabold {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                        </div>

                        <p class="mt-2 text-sm leading-7 text-slate-500">
                            مشاهده اطلاعات مشتری، پرداخت، مبالغ، آیتم‌های سفارش و مدیریت وضعیت سفارش.
                        </p>
                    </div>

                    <a href="{{ route('admin.orders.index') }}"
                       class="inline-flex h-10 w-fit items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                        بازگشت به سفارش‌ها
                    </a>
                </div>

                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-5">
                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="text-xs font-bold text-slate-500">مبلغ اصلی</div>
                        <div class="mt-2 text-lg font-black text-slate-900">
                            {{ $money($subtotalAmount) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-cyan-100 bg-cyan-50/60 p-4 shadow-sm">
                        <div class="text-xs font-bold text-cyan-700">میزان تخفیف</div>
                        <div class="mt-2 text-lg font-black text-cyan-700">
                            {{ $money($discountAmount) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4 shadow-sm">
                        <div class="text-xs font-bold text-emerald-700">مبلغ پرداختی کاربر</div>
                        <div class="mt-2 text-lg font-black text-emerald-800">
                            {{ $money($totalAmount) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-4 shadow-sm">
                        <div class="text-xs font-bold text-amber-700">مالیات{{ $taxRate !== '0' ? " ({$taxRate}٪)" : '' }}</div>
                        <div class="mt-2 text-lg font-black text-amber-800">
                            {{ $money($taxAmount) }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                        <div class="text-xs font-bold text-slate-500">تاریخ سفارش</div>
                        <div class="mt-2 text-lg font-black text-slate-900">
                            {{ $orderDate }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Customer + Payment --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="xl:col-span-7">
                    <div class="h-full bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-slate-900">اطلاعات مشتری</h2>
                                <p class="mt-1 text-xs text-slate-500">مشخصات کاربر ثبت‌کننده سفارش</p>
                            </div>

                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M20 21a8 8 0 0 0-16 0M12 13a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"
                                          stroke="currentColor"
                                          stroke-width="2"
                                          stroke-linecap="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="text-xs font-bold text-slate-500">نام و نام خانوادگی</div>
                                <div class="mt-2 text-sm font-extrabold text-slate-900">
                                    {{ "$user->first_name $user->last_name" }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <div class="text-xs font-bold text-slate-500">موبایل</div>
                                <div class="mt-2 text-sm font-extrabold text-slate-900" dir="ltr">
                                    {{ $user->mobile }}
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                                <div class="text-xs font-bold text-slate-500">ایمیل</div>
                                <div class="mt-2 break-all text-sm font-extrabold text-slate-900" dir="ltr">
                                    {{ $user->email }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-5">
                    <div class="h-full bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-base font-black text-slate-900">اطلاعات پرداخت</h2>
                                <p class="mt-1 text-xs text-slate-500">آخرین پرداخت ثبت‌شده برای سفارش</p>
                            </div>

                            <div class="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M3 7h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Zm0 0V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1M7 14h4"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <span class="text-xs font-bold text-slate-500">درگاه</span>
                                <span class="text-sm font-extrabold text-slate-900">{{ $gateway }}</span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <span class="text-xs font-bold text-slate-500">وضعیت پرداخت</span>
                                <span
                                    class="text-sm font-extrabold text-slate-900">{{ $paymentStatus }}</span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <span class="text-xs font-bold text-slate-500">کد پیگیری</span>
                                <span class="break-all text-left text-sm font-extrabold text-slate-900" dir="ltr">
                                        {{ $paymentTrackingCode }}
                                    </span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <span class="text-xs font-bold text-slate-500">تاریخ پرداخت</span>
                                <span class="text-sm font-extrabold text-slate-900">
                                        {{ $paidAt }}
                                    </span>
                            </div>

                            @if($order->coupon_code)
                                <div
                                    class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <span class="text-xs font-bold text-slate-500">کدتخفیف</span>
                                    <span class="text-sm font-extrabold text-slate-900">
                                        {{ $order->coupon_code }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div class="xl:col-span-7">
                    {{-- Status Update --}}
                    <div class="bg-white h-full rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="mb-5">
                            <h2 class="text-base font-black text-slate-900">تغییر وضعیت سفارش</h2>
                            <p class="mt-1 text-sm leading-7 text-slate-500">
                                با تغییر وضعیت، دسترسی‌های خرید به‌صورت خودکار همگام‌سازی می‌شوند. برای مثال با
                                پرداخت‌شده
                                شدن سفارش، دسترسی محصولات فعال می‌شود.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.orders.update', $order) }}"
                              x-data="{ selectedStatus: @js(old('status', $order->status->value)), refundMethod: @js(old('refund_method', '')) }"
                              class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                            @csrf
                            @method('PUT')

                            <div class="lg:col-span-5">
                                <label for="status" class="mb-2 block text-xs font-extrabold text-slate-600">
                                    وضعیت سفارش
                                </label>

                                <select id="status"
                                        name="status"
                                        x-model="selectedStatus"
                                        class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100">
                                    @foreach($statuses as $value => $label)
                                        <option
                                            value="{{ $value }}" @selected(old('status', $order->status->value) === $value)
                                            @disabled($refund && $order->status === \App\Domain\Commerce\Enums\OrderStatusEnum::REFUNDED && $value === 'paid')>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('status')
                                <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            @if($order->status === \App\Domain\Commerce\Enums\OrderStatusEnum::PAID && $totalAmount > 0)
                                <div class="lg:col-span-12 rounded-2xl border border-sky-200 bg-sky-50 p-4"
                                     x-show="selectedStatus === 'refunded'" x-cloak>
                                    <label for="refund_method" class="mb-2 block text-xs font-extrabold text-slate-700">
                                        روش استرداد مبلغ {{ $money($totalAmount) }}
                                    </label>
                                    <select id="refund_method" name="refund_method" x-model="refundMethod"
                                            x-bind:disabled="selectedStatus !== 'refunded'"
                                            x-bind:required="selectedStatus === 'refunded'"
                                            class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none focus:border-brand-300 focus:ring-4 focus:ring-brand-100">
                                        <option value="">انتخاب روش استرداد</option>
                                        <option value="wallet">بازگشت خودکار به کیف پول</option>
                                        <option value="manual_bank_transfer">واریز دستی به حساب کاربر</option>
                                    </select>
                                    @error('refund_method')
                                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                                    @enderror

                                    <p class="mt-3 text-xs leading-6 text-slate-600" x-show="refundMethod === 'wallet'" x-cloak>
                                        با ثبت وضعیت، کل مبلغ سفارش به موجودی کیف پول کاربر افزوده می‌شود و برای خرید قابل استفاده خواهد بود.
                                    </p>

                                    <div class="mt-4" x-show="refundMethod === 'manual_bank_transfer'" x-cloak>
                                        <p class="text-xs leading-6 text-slate-600">
                                            واریز به حساب کاربر به‌صورت دستی و خارج از سامانه انجام می‌شود. این انتخاب موجودی کیف پول را تغییر نمی‌دهد.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <div class="lg:col-span-7">
                                <button type="submit"
                                        class="cursor-pointer inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800 lg:w-auto">
                                    ذخیره وضعیت سفارش
                                </button>
                            </div>
                        </form>

                        @if($refund)
                            <div class="mt-5 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                                <div class="font-black">استرداد ثبت‌شده</div>
                                <div class="mt-2">روش: {{ $refund->method->label() }}</div>
                                <div>مبلغ: {{ $money($refund->amount) }}</div>
                                <div>تاریخ: {{ jalali_date($refund->created_at) }}</div>
                                @if($refund->walletTransaction)
                                    <div>شناسه تراکنش کیف پول: {{ $refund->walletTransaction->public_token }}</div>
                                @endif
                                @if($refund->manual_reference)
                                    <div>شماره پیگیری: {{ $refund->manual_reference }}</div>
                                @endif
                            </div>
                        @elseif($order->status === \App\Domain\Commerce\Enums\OrderStatusEnum::REFUNDED)
                            <p class="mt-5 text-xs text-slate-500">روش استرداد این سفارش در سوابق قدیمی ثبت نشده است.</p>
                        @endif
                    </div>
                </div>

                <div class="xl:col-span-5">
                    {{-- Description --}}
                    <div class="bg-white h-full rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="mb-5">
                            <h2 class="text-base font-black text-slate-900">توضیحات سفارش</h2>
                            <p class="mt-1 text-sm leading-7 text-slate-500">
                                هر توضیح به‌صورت جداگانه ثبت می‌شود و برای خریدار قابل مشاهده است.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.orders.descriptions.store', $order) }}">
                            @csrf

                            <x-admin.form.textarea
                                name="description"
                                :value="old('description', '')"
                                placeholder="توضیح جدید را بنویسید..."
                                required
                            />

                            @error('description')
                                <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-3 flex flex-row-reverse">
                                <button type="submit"
                                        class="cursor-pointer inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800 lg:w-auto">
                                    افزودن توضیح
                                </button>
                            </div>
                        </form>

                        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                            @forelse($order->descriptions as $description)
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="text-xs font-bold text-slate-500">
                                        {{ jalali_date($description->created_at, 'Y/m/d H:i') }}
                                        @if($description->is_legacy)
                                            <span class="mr-1">(تاریخ تقریبی؛ توضیح قدیمی)</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 whitespace-pre-wrap break-words text-sm leading-7 text-slate-800">{{ $description->body }}</div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500">هنوز توضیحی برای این سفارش ثبت نشده است.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            @if(in_array($statusValue, ['paid', 'refunded'], true))
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-black text-slate-900">تقسیم درآمد سفارش</h2>
                    <p class="mt-1 text-xs text-slate-500">بر اساس مبلغ نهایی آیتم‌ها پس از تخفیف، بدون مالیات؛ سفارش‌های قدیمی با سهم ۱۰۰٪ پلتفرم نمایش داده می‌شوند.</p>
                    @if($statusValue === 'refunded')
                        <p class="mt-2 text-xs font-bold text-rose-600">این سفارش مسترد شده و سهم نمایش‌داده‌شده قابل پرداخت نیست.</p>
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4 text-sm font-bold text-slate-700">سهم پلتفرم: {{ $money($order->items->sum(fn ($item) => $item->platformCommissionAmount())) }}</div>
                        <div class="rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-800">سهم فروشگاه‌ها: {{ $money($order->items->sum(fn ($item) => $item->sellerCommissionAmount())) }}</div>
                    </div>
                </div>
            @endif
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900">آیتم‌های خریداری‌شده</h2>
                        <p class="mt-1 text-xs text-slate-500">جزئیات آیتم‌های خریداری شده توسط کاربر</p>
                    </div>

                    <span
                        class="inline-flex w-fit items-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-extrabold text-slate-700">
                            {{ $rootItems->count() }} آیتم اصلی
                        </span>
                </div>

                <div class="p-5">
                    @if($rootItems->isEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center">
                            <div class="text-sm font-extrabold text-slate-600">آیتمی برای این سفارش ثبت نشده است.
                            </div>
                        </div>
                    @else
                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-right">
                                    <thead class="bg-slate-50">
                                    <tr>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            آیتم
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            تعداد
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            قیمت واحد
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            تخفیف
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            جمع
                                        </th>
                                        <th class="whitespace-nowrap px-4 py-4 text-xs font-black text-slate-600">
                                            دسترسی
                                        </th>
                                    </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($rootItems as $item)
                                        @php
                                            $children = $item->children ?? collect();

                                            $itemTitle = data_get($item->snapshot, 'title')
                                                ?? data_get($item->snapshot, 'product_title')
                                                ?? data_get($item->product, 'title')
                                                ?? data_get($item->purchasable, 'title')
                                                ?? 'آیتم سفارش';

                                            $itemVariantTitle = data_get($item->snapshot, 'product_sale_item_title')
                                                ?? data_get($item->snapshot, 'sale_item_title')
                                                ?? data_get($item->snapshot, 'variation_title')
                                                ?? data_get($item->snapshot, 'attributes_label');

                                            if (!$itemVariantTitle && $item->purchasable instanceof ProductSaleItem) {
                                                $itemVariantTitle = $item->purchasable->title;
                                            }

                                            if ($itemVariantTitle === $itemTitle) {
                                                $itemVariantTitle = null;
                                            }

                                            $itemQuantity = (int) ($item->quantity ?? 1);
                                            $itemUnitPrice = (int) ($item->unit_price ?? 0);
                                            $itemDiscount = (int) ($item->discount_amount ?? 0);
                                            $itemTotal = (int) ($item->total_amount ?? ($itemUnitPrice * $itemQuantity));

                                            $access = $item->purchaseAccess;
                                            $accessStatus = $access?->status;
                                            $accessStatusValue = $accessStatus?->value ?? null;
                                            $accessStatusLabel = $accessStatus?->label() ?? 'بدون دسترسی';

                                            $accessClasses = match ($accessStatusValue) {
                                                'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                'expired' => 'border-amber-200 bg-amber-50 text-amber-700',
                                                'revoked' => 'border-red-200 bg-red-50 text-red-700',
                                                default => 'border-slate-200 bg-slate-50 text-slate-500',
                                            };
                                        @endphp

                                        <tr class="align-top transition hover:bg-slate-50/70">
                                            <td class="min-w-80 px-4 py-4">
                                                <div class="text-sm font-black leading-7 text-slate-900">
                                                    {{ $itemTitle }}
                                                </div>

                                                @if(filled($itemVariantTitle))
                                                    <div class="mt-1 text-xs font-bold text-slate-600">
                                                        متغیر: <span class="font-black text-brand-700">{{ $itemVariantTitle }}</span>
                                                    </div>
                                                @endif

                                                @if(data_get($item->snapshot, 'sku'))
                                                    <div class="mt-1 text-xs font-bold text-slate-400" dir="ltr">
                                                        SKU: {{ data_get($item->snapshot, 'sku') }}
                                                    </div>
                                                @endif

                                                @if(in_array($statusValue, ['paid', 'refunded'], true))
                                                    <div class="mt-2 text-xs font-bold text-slate-600">
                                                        سهم فروشگاه {{ number_format($item->sellerCommissionBasisPoints() / 100, 2) }}٪: {{ $money($item->sellerCommissionAmount()) }}
                                                        <span class="mx-1 text-slate-300">|</span>
                                                        سهم پلتفرم {{ number_format($item->platformCommissionBasisPoints() / 100, 2) }}٪: {{ $money($item->platformCommissionAmount()) }}
                                                    </div>
                                                @endif

                                                @if($children->isNotEmpty())
                                                    <div class="mt-3 space-y-2">
                                                        @foreach($children as $child)
                                                            @php
                                                                $childTitle = data_get($child->snapshot, 'title')
                                                                    ?? data_get($child->purchasable, 'title')
                                                                    ?? 'خدمت افزوده';

                                                                $childQuantity = (int) ($child->quantity ?? 1);
                                                                $childUnitPrice = (int) ($child->unit_price ?? 0);
                                                                $childDiscount = (int) ($child->discount_amount ?? 0);
                                                                $childTotal = (int) ($child->total_amount ?? ($childUnitPrice * $childQuantity));
                                                            @endphp

                                                            <div
                                                                class="rounded-2xl border border-brand-100 bg-brand-50/60 p-3">
                                                                <div
                                                                    class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                                    <div>
                                                                        <div
                                                                            class="text-xs font-black text-brand-900">
                                                                            + {{ $childTitle }}
                                                                        </div>

                                                                        <div
                                                                            class="mt-1 text-[11px] font-bold text-brand-700">
                                                                            تعداد: {{ $childQuantity }}
                                                                        </div>
                                                                    </div>

                                                                    <div
                                                                        class="grid grid-cols-1 gap-1 text-[11px] font-bold text-brand-800 sm:text-left">
                                                                        <span>واحد: {{ $money($childUnitPrice) }}</span>

                                                                        @if($childDiscount > 0)
                                                                            <span>تخفیف: {{ $money($childDiscount) }}</span>
                                                                        @endif

                                                                        <span
                                                                            class="font-black">جمع: {{ $money($childTotal) }}</span>
                                                                        @if(in_array($statusValue, ['paid', 'refunded'], true))
                                                                            <span>سهم فروشگاه {{ number_format($child->sellerCommissionBasisPoints() / 100, 2) }}٪: {{ $money($child->sellerCommissionAmount()) }}</span>
                                                                            <span>سهم پلتفرم {{ number_format($child->platformCommissionBasisPoints() / 100, 2) }}٪: {{ $money($child->platformCommissionAmount()) }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-sm font-extrabold text-slate-800">
                                                {{ $itemQuantity }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-sm font-extrabold text-slate-800">
                                                {{ $money($itemUnitPrice) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-sm font-extrabold text-emerald-700">
                                                {{ $money($itemDiscount) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4 text-sm font-black text-slate-900">
                                                {{ $money($itemTotal) }}
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-4">
                                                    <span
                                                        class="inline-flex rounded-xl border px-2.5 py-1 text-[11px] font-extrabold {{ $accessClasses }}">
                                                        {{ $accessStatusLabel }}
                                                    </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>

                                    <tfoot class="bg-slate-50">
                                    <tr>
                                        <td colspan="4"
                                            class="px-4 py-4 text-left text-sm font-black text-slate-700">
                                            جمع کل سفارش
                                        </td>
                                        <td colspan="2"
                                            class="whitespace-nowrap px-4 py-4 text-sm font-black text-brand-800">
                                            {{ $money($totalAmount) }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-admin.page>
@endsection
