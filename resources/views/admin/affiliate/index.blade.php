@extends('admin.layout')

@section('title', 'همکاران در فروش')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="max-w-2xl text-sm leading-7 text-slate-500">
                همکاران در فروش با کد اختصاصی (که می‌تواند همزمان کد تخفیف باشد) یا لینک معرفی، سفارش به خود منتسب می‌کنند
                و از هر سفارش پرداخت‌شده کمیسیون می‌گیرند. تنظیمات سراسری از
                <a href="{{ route('admin.settings.affiliate') }}" class="font-bold text-brand-600 hover:underline">تنظیمات همکاری</a>
                قابل تغییر است.
            </p>

            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('admin.affiliate-withdrawals.index') }}"
                   class="inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 px-4 text-xs font-extrabold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                    درخواست‌های برداشت
                </a>
                <a href="{{ route('admin.affiliates.create') }}"
                   class="inline-flex h-10 items-center gap-2 rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600">
                    + همکار جدید
                </a>
            </div>
        </div>

        <form method="get" class="mb-4 flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="جستجوی نام، ایمیل، موبایل یا کد همکار..."
                   class="h-10 min-w-64 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 outline-none transition focus:border-brand-400">
            <button type="submit" class="h-10 rounded-xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:bg-slate-800">
                جستجو
            </button>
        </form>

        <x-admin.metabox title="لیست همکاران">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                        <th class="px-3 py-3 font-bold">کد</th>
                        <th class="px-3 py-3 font-bold">همکار</th>
                        <th class="px-3 py-3 font-bold">کمیسیون</th>
                        <th class="px-3 py-3 font-bold">سفارش‌ها</th>
                        <th class="px-3 py-3 font-bold">کمیسیون فعال</th>
                        <th class="px-3 py-3 font-bold">وضعیت</th>
                        <th class="px-3 py-3 font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($affiliates as $affiliate)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-3 font-black tracking-wider text-slate-800" dir="ltr">{{ $affiliate->code }}</td>
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.affiliates.show', $affiliate) }}" class="font-extrabold text-slate-800 hover:text-brand-600">
                                    {{ $affiliate->user?->first_name }} {{ $affiliate->user?->last_name }}
                                </a>
                                <div class="mt-0.5 text-[11px] text-slate-400" dir="ltr">{{ $affiliate->user?->mobile }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-600">
                                @if($affiliate->commission_type->value === 'percent')
                                    {{ rtrim(rtrim(number_format($affiliate->commission_value, 2, '.', ''), '0'), '.') }}٪
                                @else
                                    {{ number_format((int) $affiliate->commission_value) }} ت
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ number_format($affiliate->orders_count) }}</td>
                            <td class="px-3 py-3 text-xs font-black text-emerald-600" dir="ltr">{{ number_format((int) $affiliate->active_commission) }}</td>
                            <td class="px-3 py-3">
                                @if($affiliate->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">فعال</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-400">غیرفعال</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.affiliates.show', $affiliate) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                                        جزئیات
                                    </a>
                                    <a href="{{ route('admin.affiliates.edit', $affiliate) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                                        ویرایش
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-xs text-slate-400">
                                همکاری یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $affiliates->links() }}
            </div>
        </x-admin.metabox>
    </x-admin.page>
@endsection
