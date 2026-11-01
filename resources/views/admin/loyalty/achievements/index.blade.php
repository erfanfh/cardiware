@extends('admin.layout')

@section('title', 'نشان‌های باشگاه')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="max-w-2xl text-sm leading-7 text-slate-500">
                نشان‌ها با کوئری تجمیعی از وضعیت واقعی کاربر ارزیابی می‌شوند (بعد از خرید، دیدگاه یا بازدید روزانه)
                و یک‌بار برای همیشه اعطا می‌شوند. پاداش نشان‌ها XP نمی‌دهد.
            </p>

            <a href="{{ route('admin.loyalty-achievements.create') }}"
               class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600">
                + نشان جدید
            </a>
        </div>

        <x-admin.metabox title="نشان‌ها">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                        <th class="px-3 py-3 font-bold">ترتیب</th>
                        <th class="px-3 py-3 font-bold">نشان</th>
                        <th class="px-3 py-3 font-bold">معیار</th>
                        <th class="px-3 py-3 font-bold">آستانه</th>
                        <th class="px-3 py-3 font-bold">پاداش</th>
                        <th class="px-3 py-3 font-bold">دارندگان</th>
                        <th class="px-3 py-3 font-bold">وضعیت</th>
                        <th class="px-3 py-3 font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($achievements as $i => $achievement)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-2 font-extrabold text-slate-800">
                                    <span class="text-lg">{{ $achievement->badge }}</span>
                                    {{ $achievement->title }}
                                </span>
                                <div class="mt-0.5 text-[11px] text-slate-400">{{ $achievement->description }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ $achievement->metric->label() }}</td>
                            <td class="px-3 py-3 font-bold" dir="ltr">{{ number_format($achievement->threshold) }}</td>
                            <td class="px-3 py-3 font-bold text-emerald-600" dir="ltr">
                                @if($achievement->reward_points > 0)
                                    +{{ number_format($achievement->reward_points) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ number_format($achievement->holders_count) }}</td>
                            <td class="px-3 py-3">
                                @if($achievement->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">فعال</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-400">غیرفعال</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.loyalty-achievements.edit', $achievement) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                                        ویرایش
                                    </a>
                                    <form method="post" action="{{ route('admin.loyalty-achievements.destroy', $achievement) }}"
                                          class="confirmationForm js-confirm"
                                          data-confirm-title="حذف نشان"
                                          data-confirm-text="این نشان حذف شود؟">
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
