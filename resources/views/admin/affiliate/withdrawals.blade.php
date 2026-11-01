@extends('admin.layout')

@section('title', 'درخواست‌های برداشت کمیسیون')

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

        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="max-w-2xl text-sm leading-7 text-slate-500">
                درخواست‌های در انتظار در ابتدای لیست می‌آیند. با «تأیید و پرداخت» موجودی از حساب همکار کم می‌شود؛
                «رد» مبلغ را به موجودی قابل برداشت او برمی‌گرداند.
            </p>
        </div>

        <x-admin.metabox title="درخواست‌های برداشت">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                        <th class="px-3 py-3 font-bold">همکار</th>
                        <th class="px-3 py-3 font-bold">مبلغ</th>
                        <th class="px-3 py-3 font-bold">مقصد</th>
                        <th class="px-3 py-3 font-bold">یادداشت همکار</th>
                        <th class="px-3 py-3 font-bold">تاریخ</th>
                        <th class="px-3 py-3 font-bold">وضعیت</th>
                        <th class="px-3 py-3 font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($withdrawals as $withdrawal)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.affiliates.show', $withdrawal->affiliate) }}" class="font-extrabold text-slate-800 hover:text-brand-600">
                                    {{ $withdrawal->affiliate->user?->first_name }} {{ $withdrawal->affiliate->user?->last_name }}
                                </a>
                                <div class="mt-0.5 text-[11px] tracking-wider text-slate-400" dir="ltr">{{ $withdrawal->affiliate->code }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs font-black text-slate-800" dir="ltr">{{ number_format($withdrawal->amount) }}</td>
                            <td class="px-3 py-3 text-xs text-slate-600" dir="ltr">{{ $withdrawal->destination ?? '—' }}</td>
                            <td class="max-w-48 truncate px-3 py-3 text-xs text-slate-500">{{ $withdrawal->user_note ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs text-slate-500">{{ jalali_date($withdrawal->requested_at) }}</td>
                            <td class="px-3 py-3">
                                @if($withdrawal->status->value === 'approved')
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">پرداخت شده</span>
                                @elseif($withdrawal->status->value === 'rejected')
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-black text-rose-500">رد شده</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-black text-amber-600">در انتظار</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if($withdrawal->status->value === 'pending')
                                    <form method="post" action="{{ route('admin.affiliate-withdrawals.resolve', $withdrawal) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="decision" value="approve">
                                        <input type="hidden" name="note" value="">
                                        <button type="submit"
                                                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-100">
                                            تأیید و پرداخت
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.affiliate-withdrawals.resolve', $withdrawal) }}" class="mt-1">
                                        @csrf
                                        <input type="hidden" name="decision" value="reject">
                                        <input type="hidden" name="note" value="">
                                        <button type="submit"
                                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50">
                                            رد درخواست
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-slate-400">{{ $withdrawal->admin_note ?? '—' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-xs text-slate-400">درخواستی وجود ندارد.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $withdrawals->links() }}
            </div>
        </x-admin.metabox>
    </x-admin.page>
@endsection
