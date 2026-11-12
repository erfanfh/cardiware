@extends('admin.layout')

@section('title', 'تایید بیوهای باشگاه VIP')

@section('content')
    <x-admin.page>
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-black text-slate-900">تایید بیوهای باشگاه VIP</h1>
                <p class="mt-1 text-xs font-bold leading-6 text-slate-500">
                    متن معرفی اعضای VIP فقط پس از تایید شما روی کارت عمومی‌شان نمایش داده می‌شود.
                    تا وقتی «در انتظار تایید» است، کاربر نمی‌تواند متن یا تم را تغییر دهد.
                </p>
            </div>
        </div>

        {{-- فیلتر وضعیت --}}
        <div class="mb-5 flex flex-wrap gap-2">
            @foreach([
                'pending' => 'در انتظار تایید (' . number_format($counts['pending']) . ')',
                'approved' => 'تایید شده (' . number_format($counts['approved']) . ')',
                'rejected' => 'رد شده (' . number_format($counts['rejected']) . ')',
                'all' => 'همه (' . number_format($counts['all']) . ')',
            ] as $key => $label)
                <a href="{{ route('admin.users.club-bios.index', ['status' => $key]) }}"
                   class="rounded-xl px-3.5 py-2 text-xs font-black transition {{ $status === $key
                       ? 'bg-slate-900 text-white'
                       : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($bios->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center dark:border-slate-700 dark:bg-slate-800">
                <p class="font-bold text-slate-700 dark:text-slate-200">موردی برای نمایش وجود ندارد</p>
                <p class="mt-2 text-xs text-slate-500">در این وضعیت بیویی ثبت نشده است.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($bios as $user)
                    @php
                        $statusKey = (string)($user->club_bio_status ?? 'none');
                        $statusStyles = [
                            'pending' => ['در انتظار تایید', 'bg-amber-100 text-amber-800'],
                            'approved' => ['تایید شده', 'bg-emerald-100 text-emerald-700'],
                            'rejected' => ['رد شده', 'bg-rose-100 text-rose-700'],
                            'none' => ['ثبت نشده', 'bg-slate-100 text-slate-600'],
                        ];
                        [$statusLabel, $statusClass] = $statusStyles[$statusKey] ?? $statusStyles['none'];
                        $themeLabel = \App\Application\Loyalty\Support\VipClubProfile::theme($user->club_theme)['label'];
                    @endphp
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex min-w-0 flex-wrap items-center gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-sm font-black text-slate-900 hover:text-brand-600 dark:text-white">
                                    {{ $user->display_name ?: $user->username }}
                                </a>
                                <span class="rounded-lg px-2 py-1 text-[10px] font-black {{ $statusClass }}">{{ $statusLabel }}</span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500 dark:bg-slate-700 dark:text-slate-300">تم: {{ $themeLabel }}</span>
                                @if($user->club_bio_edited_at)
                                    <span class="text-[10px] font-bold text-slate-400">{{ jalali_date($user->club_bio_edited_at) }}</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if($statusKey !== 'approved')
                                    <form method="POST" action="{{ route('admin.users.club-bio.moderate', $user) }}"
                                          onsubmit="return confirm('تایید می‌شود؟')">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="from" value="club-bios">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="rounded-xl bg-emerald-600 px-3 py-2 text-[11px] font-black text-white transition hover:bg-emerald-700">تایید</button>
                                    </form>
                                @endif
                                @if($statusKey !== 'rejected')
                                    <form method="POST" action="{{ route('admin.users.club-bio.moderate', $user) }}"
                                          onsubmit="return confirm('رد می‌شود؟ کاربر می‌تواند یک‌بار دیگر بنویسد.')">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="from" value="club-bios">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="rounded-xl bg-rose-600 px-3 py-2 text-[11px] font-black text-white transition hover:bg-rose-700">رد</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <p class="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-3 text-sm font-bold leading-7 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            {{ filled($user->club_bio) ? '«' . $user->club_bio . '»' : '— متن حذف شده —' }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                {{ $bios->links() }}
            </div>
        @endif
    </x-admin.page>
@endsection
