@extends('admin.layout')

@section('title', 'درخواست وجه فروشگاه‌ها')

@section('content')
    <x-admin.page>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-orange-100 bg-gradient-to-l from-white to-orange-50/60 p-6 text-slate-900 shadow-sm">
            <div><div class="text-xs font-bold text-orange-600">عملیات مالی فروشگاه‌ها</div><h1 class="mt-2 text-xl font-black">درخواست وجه فروشگاه‌ها</h1><p class="mt-2 text-xs leading-6 text-slate-600">درخواست‌های در انتظار از ماندهٔ قابل درخواست کنار گذاشته می‌شوند. تأیید، مبلغ را از ماندهٔ فروشگاه کسر می‌کند.</p></div>
            <div class="rounded-2xl border border-orange-100 bg-white px-5 py-3 text-center shadow-sm"><div class="text-2xl font-black">{{ number_format($pendingCount) }}</div><div class="text-xs text-slate-600">در انتظار بررسی</div></div>
        </div>

        @if(session('success'))<div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-xl bg-rose-50 p-4 text-sm font-bold text-rose-700">{{ session('error') }}</div>@endif
        <x-admin.form.validation-errors class="mb-5"/>

        @can('store.withdrawals')
            <x-admin.metabox title="حداقل و حداکثر مبلغ درخواست" :collapsible="false">
                <x-admin.form action="{{ route('admin.store-withdrawals.settings') }}" method="put" class="flex flex-wrap items-end gap-4">
                    <x-admin.field label="حداقل مبلغ (تومان)" required><x-admin.form.input name="minimum_amount" type="number" min="1" :value="old('minimum_amount', $minimum)"/></x-admin.field>
                    <x-admin.field label="حداکثر مبلغ (تومان)" required><x-admin.form.input name="maximum_amount" type="number" min="1" :value="old('maximum_amount', $maximum)"/></x-admin.field>
                    <x-admin.form.button type="submit" variant="solid">ذخیره محدودیت‌ها</x-admin.form.button>
                </x-admin.form>
            </x-admin.metabox>
        @endcan

        <div class="mt-5">
            <x-admin.metabox title="فهرست درخواست‌ها" :collapsible="false">
                <form method="get" class="mb-5 flex flex-wrap items-end gap-3">
                    <x-admin.field label="وضعیت"><x-admin.form.select name="status" :options="['pending' => 'در انتظار بررسی', 'approved' => 'تأییدشده', 'rejected' => 'ردشده']" :value="request('status')" placeholder="همه وضعیت‌ها"/></x-admin.field>
                    <x-admin.form.button type="submit" variant="secondary">اعمال فیلتر</x-admin.form.button>
                    <x-admin.form.button tag="a" href="{{ route('admin.store-withdrawals.index') }}" variant="secondary">حذف فیلتر</x-admin.form.button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-right text-sm">
                        <thead class="border-b border-slate-200 text-xs text-slate-500"><tr><th class="p-3">فروشگاه</th><th class="p-3">ثبت‌کننده</th><th class="p-3">مبلغ</th><th class="p-3">حساب مقصد</th><th class="p-3">وضعیت</th><th class="p-3">تاریخ</th><th class="p-3"></th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($withdrawals as $withdrawal)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="p-3 font-bold">{{ $withdrawal->store?->title ?? 'فروشگاه حذف‌شده' }}</td>
                                    <td class="p-3">{{ $withdrawal->requestedBy?->display_name ?? 'کاربر حذف‌شده' }}</td>
                                    <td class="whitespace-nowrap p-3 font-black">{{ number_format($withdrawal->amount) }} تومان</td>
                                    <td class="p-3 text-xs" dir="ltr">{{ $withdrawal->maskedIban() }}</td>
                                    <td class="p-3"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ match($withdrawal->status->value) { 'approved' => 'bg-emerald-50 text-emerald-700', 'rejected' => 'bg-rose-50 text-rose-700', default => 'bg-amber-50 text-amber-700' } }}">{{ $withdrawal->status->label() }}</span></td>
                                    <td class="whitespace-nowrap p-3 text-xs text-slate-500">{{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</td>
                                    <td class="p-3"><a class="font-bold text-brand-600 hover:text-brand-700" href="{{ route('admin.store-withdrawals.show', $withdrawal) }}">بررسی ←</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="p-10 text-center text-slate-500">درخواستی برای نمایش وجود ندارد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">{{ $withdrawals->links() }}</div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
