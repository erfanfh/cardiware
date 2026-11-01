@extends('admin.layout')

@section('title', 'سطوح باشگاه مخاطبان')

@section('content')
    <x-admin.page>
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="max-w-2xl text-sm leading-7 text-slate-500">
                سطح کاربر فقط با XP (کل امتیاز کسب‌شده، ستون <code>lifetime_xp</code>) تعیین می‌شود و با خرج کردن امتیاز
                پایین نمی‌آید. با رسیدن به هر سطح، پاداش آن یک‌بار به کاربر داده می‌شود و ضریب خرید او در امتیاز
                خرید اعمال می‌گردد.
            </p>

            <a href="{{ route('admin.loyalty-levels.create') }}"
               class="inline-flex h-10 shrink-0 items-center gap-2 rounded-xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600">
                + سطح جدید
            </a>
        </div>

        <x-admin.metabox title="سطوح (به ترتیب XP)">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-200 text-right text-xs text-slate-400">
                        <th class="px-3 py-3 font-bold">ترتیب</th>
                        <th class="px-3 py-3 font-bold">سطح</th>
                        <th class="px-3 py-3 font-bold">حداقل XP</th>
                        <th class="px-3 py-3 font-bold">پاداش رسیدن</th>
                        <th class="px-3 py-3 font-bold">ضریب خرید</th>
                        <th class="px-3 py-3 font-bold">اعضا</th>
                        <th class="px-3 py-3 font-bold">وضعیت</th>
                        <th class="px-3 py-3 font-bold">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($levels as $i => $level)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-3 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-3">
                                <span class="inline-flex items-center gap-2 font-extrabold text-slate-800">
                                    <span class="text-lg">{{ $level->badge }}</span>
                                    {{ $level->title }}
                                </span>
                            </td>
                            <td class="px-3 py-3 font-bold" dir="ltr">{{ number_format($level->min_xp) }}</td>
                            <td class="px-3 py-3 text-emerald-600" dir="ltr">
                                @if($level->reward_points > 0)
                                    +{{ number_format($level->reward_points) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-3 py-3 font-bold text-cyan-600" dir="ltr">
                                ×{{ rtrim(rtrim(number_format($level->purchase_multiplier, 2, '.', ''), '0'), '.') }}
                            </td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-500">{{ number_format($level->members_count) }}</td>
                            <td class="px-3 py-3">
                                @if($level->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-black text-emerald-600">فعال</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-black text-slate-400">غیرفعال</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.loyalty-levels.edit', $level) }}"
                                       class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-bold text-slate-600 transition hover:border-brand-300 hover:text-brand-600">
                                        ویرایش
                                    </a>
                                    @if($level->min_xp > 0)
                                        <form method="post"
                                              action="{{ route('admin.loyalty-levels.destroy', $level) }}"
                                              class="confirmationForm js-confirm"
                                              data-confirm-title="حذف سطح"
                                              data-confirm-text="این سطح حذف شود؟ کاربران آن به سطح پایین‌تر برمی‌گردند.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="rounded-lg border border-rose-200 px-3 py-1.5 text-[11px] font-bold text-rose-500 transition hover:bg-rose-50">
                                                حذف
                                            </button>
                                        </form>
                                    @endif
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
