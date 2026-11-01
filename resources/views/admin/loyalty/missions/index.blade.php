@extends('admin.layout')

@section('title', 'مأموریت‌های باشگاه')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="max-w-2xl text-sm leading-7 text-slate-500">
                مأموریت‌ها هفتگی‌اند (از شنبه) و با اولین رویداد هفته بعد خودبه‌خود تازه می‌شوند.
                جایزه با رسیدن به هدف، خودکار و یک‌بار برای هر کاربر در هر هفته پرداخت می‌شود.
            </p>

            <a href="{{ route('admin.loyalty-missions.create') }}"
               class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600">
                + مأموریت جدید
            </a>
        </div>

        <x-admin.metabox title="مأموریت‌ها">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                        <th class="px-3 py-3 font-bold">ترتیب</th>
                        <th class="px-3 py-3 font-bold">مأموریت</th>
                        <th class="px-3 py-3 font-bold">معیار</th>
                        <th class="px-3 py-3 font-bold">هدف</th>
                        <th class="px-3 py-3 font-bold">جایزه</th>
                        <th class="px-3 py-3 font-bold">تکمیل‌ها</th>
                        <th class="px-3 py-3 font-bold">وضعیت</th>
                        <th class="px-3 py-3 font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($missions as $i => $mission)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-2 font-extrabold text-slate-800">
                                    <span class="text-lg">{{ $mission->icon }}</span>
                                    {{ $mission->title }}
                                </span>
                                <div class="mt-0.5 text-[11px] text-slate-400">{{ $mission->description }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ $mission->metric->label() }}</td>
                            <td class="px-3 py-3 font-bold" dir="ltr">{{ number_format($mission->target_count) }}</td>
                            <td class="px-3 py-3 font-bold text-emerald-600" dir="ltr">+{{ number_format($mission->reward_points) }}</td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ number_format($mission->completions_count) }}</td>
                            <td class="px-3 py-3">
                                @if($mission->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">فعال</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-400">غیرفعال</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.loyalty-missions.edit', $mission) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                                        ویرایش
                                    </a>
                                    <form method="post" action="{{ route('admin.loyalty-missions.destroy', $mission) }}"
                                          class="confirmationForm js-confirm"
                                          data-confirm-title="حذف مأموریت"
                                          data-confirm-text="این مأموریت حذف شود؟">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.metabox>
    </x-admin.page>
@endsection
