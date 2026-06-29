@extends('admin.layout')

@section('title', 'ویرایش سفارش ' . $order->order_number)

@php
    use Illuminate\Support\Facades\Route;

    $money = fn (?int $amount): string => number_format((int) $amount) . ' تومان';

    $rootItems = $order->rootItems ?? collect();
    $user = $order->user;
    $latestPayment = $order->latestPayment;

    $subtotalAmount = (int) ($order->subtotal_amount ?? 0);
    $discountAmount = (int) ($order->discount_amount ?? 0);
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

    $gateway = $latestPayment?->gateway;
    $paymentStatus = $latestPayment?->status;
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

                <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
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
                                <span class="text-sm font-extrabold text-slate-900">{{ $gateway->label() }}</span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <span class="text-xs font-bold text-slate-500">وضعیت پرداخت</span>
                                <span
                                    class="text-sm font-extrabold text-slate-900">{{ $paymentStatus->label() }}</span>
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
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Update --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <div class="mb-5">
                    <h2 class="text-base font-black text-slate-900">تغییر وضعیت سفارش</h2>
                    <p class="mt-1 text-sm leading-7 text-slate-500">
                        با تغییر وضعیت، دسترسی‌های خرید به‌صورت خودکار همگام‌سازی می‌شوند. برای مثال با پرداخت‌شده
                        شدن سفارش، دسترسی محصولات فعال می‌شود.
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.orders.update', $order) }}"
                      class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:items-end">
                    @csrf
                    @method('PUT')

                    <div class="lg:col-span-5">
                        <label for="status" class="mb-2 block text-xs font-extrabold text-slate-600">
                            وضعیت سفارش
                        </label>

                        <select id="status"
                                name="status"
                                class="h-11 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-800 outline-none transition focus:border-brand-300 focus:ring-4 focus:ring-brand-100">
                            @foreach($statuses as $value => $label)
                                <option
                                    value="{{ $value }}" @selected(old('status', $order->status->value) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('status')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="lg:col-span-7">
                        <button type="submit"
                                class="cursor-pointer inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800 lg:w-auto">
                            ذخیره وضعیت سفارش
                        </button>
                    </div>
                </form>
            </div>

            {{-- Items --}}
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

                                                @if(data_get($item->snapshot, 'sku'))
                                                    <div class="mt-1 text-xs font-bold text-slate-400" dir="ltr">
                                                        SKU: {{ data_get($item->snapshot, 'sku') }}
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
