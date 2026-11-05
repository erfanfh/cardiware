@extends('front.master')

@php
    // نام نمایشی یا نام کاربری؛ نام واقعی (full_name) در صفحه عمومی نمایش داده نمی‌شود.
    $memberName = collect([$member->display_name, $member->username])
        ->map(fn ($value) => trim((string) $value))
        ->first(fn ($value) => $value !== '') ?? 'عضو باشگاه';
    $colors = $level->colorClasses();
    $vipTheme = \App\Application\Loyalty\Support\VipClubProfile::theme($member->club_theme);
@endphp

@section('head')
    <title>{{ $memberName }} در باشگاه مخاطبان | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex">
@endsection

@section('content')
    <section class="mx-auto max-w-5xl space-y-5 px-4 py-6 sm:py-8">
        <section class="relative isolate overflow-hidden rounded-3xl border {{ $isVip ? 'border-amber-300 dark:border-amber-500/40' : 'border-slate-200 dark:border-slate-800' }} bg-white p-6 shadow-sm dark:bg-slate-900 {{ $isVip ? 'pt-20' : '' }}">
            @if($isVip)
                {{-- سربرگ اختصاصی VIP با تم انتخابی خود عضو --}}
                <div class="absolute inset-x-0 top-0 -z-10 h-32 bg-gradient-to-l {{ $vipTheme['header'] }}"></div>
                <div class="absolute left-4 top-4 rounded-xl bg-white/90 px-3 py-1 text-xs font-black text-amber-700 shadow-sm backdrop-blur dark:bg-slate-900/80 dark:text-amber-300">👑 عضو VIP</div>
            @else
                <div class="absolute inset-x-0 top-0 -z-10 h-28 bg-gradient-to-l {{ $colors['bar'] }} opacity-20"></div>
            @endif
            <div class="flex flex-col items-center gap-5 text-center sm:flex-row sm:text-right">
                <img src="{{ $member->profileAvatarUrl() ?: asset('img/user-avatar.png') }}" alt="{{ $memberName }}"
                     class="h-24 w-24 shrink-0 rounded-3xl object-cover {{ $isVip ? 'ring-4 ' . $vipTheme['ring'] . ' ring-offset-2 ring-offset-white dark:ring-offset-slate-900' : $colors['frame'] }}">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-black text-slate-950 dark:text-white">{{ $memberName }}</h1>
                    <div class="mt-2 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="rounded-xl border px-2.5 py-1 text-xs font-black {{ $colors['chip'] }}">{{ $level->badge }} سطح {{ $level->title }}</span>
                        @if($isVip)
                            <span class="rounded-xl border px-2.5 py-1 text-xs font-black {{ $vipTheme['chip'] }}">👑 VIP{{ $vipSince ? ' از ' . jalali_date($vipSince, 'F Y') : '' }}</span>
                        @endif
                        @if($isElite)
                            <span class="rounded-xl border border-violet-300 bg-violet-50 px-2.5 py-1 text-xs font-black text-violet-700 dark:border-violet-500/40 dark:bg-violet-500/10 dark:text-violet-300">💎 کانون نخبگان</span>
                        @endif
                        @if($monthRank)
                            <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-600 dark:bg-slate-800 dark:text-slate-300">رتبه {{ number_format($monthRank) }} این ماه</span>
                        @endif
                    </div>
                    @if($isVip && filled($member->club_bio))
                        <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">«{{ $member->club_bio }}»</p>
                    @endif
                    <div class="mt-4">
                        <div class="flex justify-between text-[11px] font-bold text-slate-500 dark:text-slate-400">
                            <span>{{ number_format($xp) }} XP</span>
                            <span>{{ $nextLevel ? number_format(max(0, $nextLevel->min_xp - $xp)) . ' XP تا ' . $nextLevel->title : 'بالاترین سطح باشگاه' }}</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full bg-gradient-to-l {{ $colors['bar'] }}" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-black text-slate-900 dark:text-white">جام‌ها و افتخارات</h2>
                <span class="rounded-xl bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ number_format($trophies->count()) }} جام</span>
            </div>
            @if($trophies->isEmpty())
                <p class="mt-3 text-xs leading-7 text-slate-500 dark:text-slate-400">هنوز جامی ثبت نشده؛ برترین‌های هر ماه، فصل و سال اینجا جام می‌گیرند.</p>
            @else
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($trophies as $trophy)
                        <div class="flex items-center gap-3 rounded-2xl border border-amber-200/70 bg-amber-50/60 p-3 dark:border-amber-500/20 dark:bg-amber-500/5">
                            <span class="text-3xl">{{ $trophy->trophy() }}</span>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-black text-slate-900 dark:text-white">{{ $trophy->title() }}</div>
                                <div class="text-[10px] font-bold text-slate-400">{{ number_format($trophy->score) }} XP در آن دوره</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-black text-slate-900 dark:text-white">نشان‌ها</h2>
            @if($achievements->isEmpty())
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">هنوز نشانی گرفته نشده.</p>
            @else
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($achievements as $achievement)
                        <span class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <span class="text-base">{{ $achievement->badge }}</span> {{ $achievement->title }}
                        </span>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="flex flex-wrap justify-center gap-2">
            <a href="{{ route('loyalty.hall-of-fame') }}" class="inline-flex h-11 items-center rounded-2xl bg-slate-950 px-5 text-xs font-black text-white dark:bg-white dark:text-slate-900">تالار افتخار</a>
            <a href="{{ route('loyalty.leaderboard') }}" class="inline-flex h-11 items-center rounded-2xl border border-slate-200 bg-white px-5 text-xs font-black text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">جدول برترین‌ها</a>
        </div>
    </section>
@endsection
