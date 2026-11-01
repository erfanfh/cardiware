@extends('admin.layout')

@section('title', 'جزئیات همکار در فروش')

@section('content')
    <x-admin.page :columns="2">
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="col-span-12 space-y-5">
            <x-admin.metabox title="خلاصه همکار">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="text-base font-black text-slate-800">
                            {{ $affiliate->user?->first_name }} {{ $affiliate->user?->last_name }}
                            <span class="text-xs font-bold text-slate-400" dir="ltr">{{ $affiliate->user?->email }} · {{ $affiliate->user?->mobile }}</span>
                        </div>
                        <div class="mt-1 text-xs font-bold text-slate-500">
                            کد همکار: <span class="tracking-widest text-slate-800" dir="ltr">{{ $affiliate->code }}</span>
                            ·
                            کمیسیون:
                            @if($affiliate->commission_type->value === 'percent')
                                {{ rtrim(rtrim(number_format($affiliate->commission_value, 2, '.', ''), '0'), '.') }}٪
                            @else
                                {{ number_format((int) $affiliate->commission_value) }} تومان
                            @endif
                            ·
                            پنجره انتساب: {{ $affiliate->attribution_days }} روز
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.affiliates.edit', $affiliate) }}"
                           class="inline-flex h-9 items-center rounded-xl border border-slate-200 px-3 text-xs font-extrabold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                            ویرایش تنظیمات
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <div class="text-base font-black text-slate-800">{{ number_format($overview['clicksCount']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-slate-500">کلیک</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <div class="text-base font-black text-slate-800">{{ number_format($overview['signupsCount']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-slate-500">ثبت‌نام</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <div class="text-base font-black text-slate-800">{{ number_format($overview['buyersCount']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-slate-500">خریدار</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <div class="text-base font-black text-slate-800">{{ number_format($overview['ordersCount']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-slate-500">سفارش</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-3 text-center">
                        <div class="text-base font-black text-slate-800">{{ number_format($overview['revenue']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-slate-500">گردش (تومان)</div>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-center">
                        <div class="text-base font-black text-emerald-700">{{ number_format($overview['totalCommission']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-emerald-600">کمیسیون فعال</div>
                    </div>
                    <div class="rounded-2xl bg-brand-50 p-3 text-center">
                        <div class="text-base font-black text-brand-700">{{ number_format($overview['availableBalance']) }}</div>
                        <div class="mt-1 text-[10px] font-bold text-brand-600">قابل برداشت</div>
                    </div>
                </div>

                @if($overview['revertedCommission'] > 0)
                    <div class="mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700">
                        {{ number_format($overview['revertedCommission']) }} تومان کمیسیون این همکار به دلیل ریفاند سفارش‌ها برگشت خورده است.
                    </div>
                @endif
            </x-admin.metabox>

            <x-admin.metabox title="سفارش‌های منتسب">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                            <th class="px-3 py-3 font-bold">سفارش</th>
                            <th class="px-3 py-3 font-bold">خریدار</th>
                            <th class="px-3 py-3 font-bold">مبلغ سفارش</th>
                            <th class="px-3 py-3 font-bold">کمیسیون</th>
                            <th class="px-3 py-3 font-bold">منبع</th>
                            <th class="px-3 py-3 font-bold">وضعیت</th>
                            <th class="px-3 py-3 font-bold">تاریخ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($overview['recentOrders'] as $affiliateOrder)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-3 py-3 text-xs font-bold text-slate-700" dir="ltr">#{{ $affiliateOrder->order?->order_number ?? $affiliateOrder->order_id }}</td>
                                <td class="px-3 py-3 text-xs font-bold text-slate-700">
                                    {{ $affiliateOrder->user?->first_name }} {{ $affiliateOrder->user?->last_name }}
                                </td>
                                <td class="px-3 py-3 text-xs font-bold text-slate-600" dir="ltr">{{ number_format($affiliateOrder->order_amount) }}</td>
                                <td class="px-3 py-3 text-xs font-black text-emerald-600" dir="ltr">{{ number_format($affiliateOrder->commission_amount) }}</td>
                                <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ $affiliateOrder->source->label() }}</td>
                                <td class="px-3 py-3">
                                    @if($affiliateOrder->status->value === 'active')
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">فعال</span>
                                    @else
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-black text-rose-500">برگشت خورده</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ jalali_date($affiliateOrder->created_at) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-8 text-center text-xs text-slate-400">سفارشی منتسب نشده است.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.metabox>

            <x-admin.metabox title="برداشت‌های همکار">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                            <th class="px-3 py-3 font-bold">مبلغ</th>
                            <th class="px-3 py-3 font-bold">وضعیت</th>
                            <th class="px-3 py-3 font-bold">مقصد</th>
                            <th class="px-3 py-3 font-bold">تاریخ درخواست</th>
                            <th class="px-3 py-3 font-bold">تاریخ تعیین تکلیف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($overview['withdrawals'] as $withdrawal)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-3 py-3 text-xs font-black text-slate-800" dir="ltr">{{ number_format($withdrawal->amount) }}</td>
                                <td class="px-3 py-3">
                                    @if($withdrawal->status->value === 'approved')
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">پرداخت شده</span>
                                    @elseif($withdrawal->status->value === 'rejected')
                                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-black text-rose-500">رد شده</span>
                                    @else
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-black text-amber-600">در انتظار</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-600" dir="ltr">{{ $withdrawal->destination ?? '—' }}</td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ jalali_date($withdrawal->requested_at) }}</td>
                                <td class="px-3 py-3 text-xs text-slate-500">{{ $withdrawal->resolved_at ? jalali_date($withdrawal->resolved_at) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-xs text-slate-400">برداشتی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
