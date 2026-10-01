@extends('admin.layout')

@section('title', 'درخواست‌های برداشت کیف پول')

@section('content')
    <x-admin.page>
        <div class="mb-5 rounded-2xl border border-orange-100 bg-gradient-to-l from-white to-orange-50/60 p-6 text-slate-900 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-xs font-bold text-orange-600">عملیات مالی</div>
                    <h1 class="mt-2 text-xl font-black">درخواست‌های برداشت</h1>
                    <p class="mt-2 text-xs leading-6 text-slate-600">درخواست‌های جدید ابتدا موجودی کاربر را رزرو می‌کنند. تأیید، رزرو را نگه می‌دارد و ثبت واریز آن را به تراکنش برداشت تبدیل می‌کند.</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-white px-5 py-3 text-center shadow-sm">
                    <div class="text-2xl font-black">{{ number_format($pendingCount) }}</div>
                    <div class="text-xs text-slate-600">در انتظار بررسی</div>
                </div>
            </div>
        </div>

        @can('wallet.settings')
            <x-admin.metabox title="دسترسی ثبت درخواست برداشت" :collapsible="false">
                <x-admin.form action="{{ route('admin.wallet-withdrawals.settings') }}" method="put" class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <x-admin.form.switch name="withdrawal_enabled" :checked="get_setting('financial', 'withdrawal_enabled', false)"/>
                        <div>
                            <div class="text-sm font-bold text-slate-800">ثبت درخواست جدید فعال باشد</div>
                            <div class="mt-1 text-xs text-slate-500">درخواست‌های ثبت‌شده حتی پس از غیرفعال کردن، قابل رسیدگی هستند.</div>
                        </div>
                    </div>
                    <x-admin.form.button type="submit" variant="solid">ذخیره وضعیت</x-admin.form.button>
                </x-admin.form>
            </x-admin.metabox>
        @endcan

        <div class="mt-5">
            <x-admin.metabox title="فهرست درخواست‌ها" :collapsible="false">
                <form method="get" class="mb-5 flex flex-wrap items-end gap-3">
                    <x-admin.field label="وضعیت">
                        <x-admin.form.select name="status" :options="['pending' => 'در انتظار بررسی', 'approved' => 'تأیید شده', 'paid' => 'واریز شده', 'rejected' => 'رد شده']" :value="request('status')" placeholder="همه وضعیت‌ها"/>
                    </x-admin.field>
                    <x-admin.form.button type="submit" variant="secondary">اعمال فیلتر</x-admin.form.button>
                    <x-admin.form.button tag="a" href="{{ route('admin.wallet-withdrawals.index') }}" variant="secondary">حذف فیلتر</x-admin.form.button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-right text-sm">
                        <thead class="border-b border-slate-200 text-xs text-slate-500">
                            <tr><th class="p-3">کاربر</th><th class="p-3">مبلغ</th><th class="p-3">حساب مقصد</th><th class="p-3">وضعیت</th><th class="p-3">تاریخ</th><th class="p-3"></th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($withdrawals as $withdrawal)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="p-3 font-bold text-slate-800">{{ $withdrawal->user?->full_name ?? 'کاربر حذف‌شده' }} <span class="text-xs text-slate-400">#{{ $withdrawal->user_id }}</span></td>
                                    <td class="p-3 whitespace-nowrap font-black">{{ number_format($withdrawal->amount) }} تومان</td>
                                    <td class="p-3 text-xs"><div dir="ltr">{{ $withdrawal->maskedIban() }}</div>@if($withdrawal->card_number)<div class="mt-1 text-slate-500" dir="ltr">{{ $withdrawal->maskedCardNumber() }}</div>@endif</td>
                                    <td class="p-3"><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $withdrawal->status->value === 'paid', 'bg-red-50 text-red-700' => $withdrawal->status->value === 'rejected', 'bg-amber-50 text-amber-700' => in_array($withdrawal->status->value, ['pending', 'approved'])])>{{ $withdrawal->status->label() }}</span></td>
                                    <td class="p-3 whitespace-nowrap text-xs text-slate-500">{{ jalali_date($withdrawal->created_at, 'Y/m/d H:i') }}</td>
                                    <td class="p-3"><a class="font-bold text-brand-600 hover:text-brand-700" href="{{ route('admin.wallet-withdrawals.show', $withdrawal) }}">بررسی ←</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-10 text-center text-slate-500">درخواستی برای نمایش وجود ندارد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-5">{{ $withdrawals->links() }}</div>
            </x-admin.metabox>
        </div>
    </x-admin.page>
@endsection
