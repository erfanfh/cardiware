@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-6 sm:py-8 lg:py-10">
        <section class="relative overflow-hidden rounded-[28px] border border-orange-200/80 bg-gradient-to-bl from-white via-orange-50 to-sky-50 text-slate-900 shadow-2xl shadow-orange-200/30 dark:border-slate-800 dark:bg-none dark:bg-slate-950 dark:text-white dark:shadow-black/30 sm:rounded-[36px]">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_18%_12%,rgba(249,115,22,.12),transparent_35%),radial-gradient(circle_at_90%_75%,rgba(14,165,233,.10),transparent_38%)] dark:bg-[radial-gradient(circle_at_18%_12%,rgba(249,115,22,.23),transparent_35%),radial-gradient(circle_at_90%_75%,rgba(14,165,233,.15),transparent_38%)]"></div>
            <div class="pointer-events-none absolute -bottom-28 -left-20 h-72 w-72 rounded-full border border-orange-300/30 dark:border-white/10"></div>
            <div class="pointer-events-none absolute -bottom-12 -left-6 h-48 w-48 rounded-full border border-orange-300/30 dark:border-white/10"></div>

            <div class="relative grid gap-9 p-6 sm:p-9 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center lg:gap-12 lg:p-12">
                <div>
                    <x-front.breadcrumbs
                        nav-class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400"
                        link-class="transition hover:text-brand-600 dark:hover:text-white"
                        current-class="text-slate-800 dark:text-white"
                    />
                    <div class="mt-8 inline-flex items-center gap-2 rounded-full border border-orange-200 bg-white/75 px-3 py-1.5 text-[11px] font-black text-brand-700 shadow-sm dark:border-orange-400/20 dark:bg-orange-400/10 dark:text-orange-200 dark:shadow-none">
                        <span class="h-1.5 w-1.5 rounded-full bg-orange-400 shadow-[0_0_10px_rgba(251,146,60,.9)]"></span>
                        صدای جامعه Cardiware
                    </div>
                    <h1 class="mt-4 text-3xl font-black leading-tight sm:text-4xl lg:text-[42px]">نظرسنجی‌ها</h1>
                    <p class="mt-4 max-w-xl text-sm leading-8 text-slate-600 dark:text-slate-300 sm:text-base">از بازی‌ها و محتواهای محبوب تا تجربه شما از سایت؛ نظر خود را ثبت کنید و در تصمیم‌های جامعه Cardiware سهیم باشید.</p>
                    <a href="#poll-list" class="mt-7 inline-flex min-h-11 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-black text-white shadow-lg shadow-brand-500/20 transition hover:-translate-y-0.5 hover:bg-brand-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 dark:focus-visible:outline-white">
                        دیدن نظرسنجی‌ها
                        <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                    </a>
                </div>

                <div class="relative grid grid-cols-2 gap-3" aria-label="آمار نظرسنجی‌ها">
                    <div class="col-span-2 rounded-3xl border border-white/90 bg-white/75 p-5 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-white/[0.07] dark:shadow-none">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">نظرسنجی‌های فعال</span>
                            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-orange-100 text-brand-600 dark:bg-orange-400/15 dark:text-orange-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.chart-03')</span>
                        </div>
                        <div class="mt-2 text-4xl font-black tabular-nums">{{ number_format($stats['active']) }}</div>
                        <div class="mt-4 flex h-1.5 gap-1 overflow-hidden rounded-full bg-slate-100 dark:bg-white/10" aria-hidden="true">
                            <span class="w-1/2 rounded-full bg-brand-400"></span><span class="w-1/3 rounded-full bg-sky-400"></span><span class="w-1/6 rounded-full bg-violet-400"></span>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-white/90 bg-white/70 p-4 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-white/[0.06] dark:shadow-none">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">در راه</div>
                        <div class="mt-2 text-2xl font-black tabular-nums">{{ number_format($stats['upcoming']) }}</div>
                    </div>
                    <div class="rounded-2xl border border-white/90 bg-white/70 p-4 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-white/[0.06] dark:shadow-none">
                        <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400">کل نظرسنجی‌ها</div>
                        <div class="mt-2 text-2xl font-black tabular-nums">{{ number_format($stats['all']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="poll-list" class="mt-7 scroll-mt-24 sm:mt-9">
            <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900 dark:text-white">نظرسنجی‌ها را مرور کنید</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">موضوع دلخواهتان را پیدا کنید و در گفتگو شرکت کنید.</p>
                    </div>
                    <form action="{{ route('polls.index') }}#poll-list" method="get" role="search" class="flex w-full max-w-md gap-2">
                        @if($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
                        <label for="poll-search" class="sr-only">جست‌وجوی نظرسنجی</label>
                        <div class="relative min-w-0 flex-1">
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400 [&_svg]:h-4 [&_svg]:w-4">@include('icons.search-01')</span>
                            <input id="poll-search" name="search" type="search" value="{{ $search }}" placeholder="جست‌وجوی نظرسنجی..." maxlength="100" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pr-10 pl-3 text-xs font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-brand-400 focus:ring-2 focus:ring-brand-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-brand-500">
                        </div>
                        <button type="submit" class="h-11 rounded-xl bg-slate-900 px-4 text-xs font-black text-white transition hover:bg-brand-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:bg-brand-500 dark:hover:bg-brand-400">جست‌وجو</button>
                    </form>
                </div>
                <div class="mt-5 flex gap-2 overflow-x-auto pb-1" aria-label="فیلتر وضعیت نظرسنجی‌ها">
                    @foreach(['all' => 'همه', 'active' => 'در حال برگزاری', 'upcoming' => 'به‌زودی', 'ended' => 'پایان‌یافته'] as $key => $label)
                        <a href="{{ route('polls.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search ?: null])) }}#poll-list" @if($status === $key) aria-current="page" @endif
                           class="inline-flex min-h-10 shrink-0 items-center gap-2 rounded-xl border px-3.5 text-xs font-black transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 {{ $status === $key ? 'border-brand-500 bg-brand-500 text-white shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:text-brand-300' }}">
                            {{ $label }}
                            <span class="rounded-md px-1.5 py-0.5 text-[10px] {{ $status === $key ? 'bg-white/20' : 'bg-white text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}">{{ number_format($stats[$key]) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($polls->isNotEmpty())
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($polls as $poll)
                        @php
                            $active = $poll->acceptsVotesAt();
                            $upcoming = $poll->starts_at?->isFuture() ?? false;
                            $excerpt = trim(strip_tags($poll->question));
                            $excerpt = \Illuminate\Support\Str::limit(html_entity_decode($excerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 155);
                        @endphp
                        <article class="group flex h-full flex-col overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-xl hover:shadow-slate-200/50 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-500/30 dark:hover:shadow-black/20">
                            <div class="h-1 bg-gradient-to-l from-brand-500 via-orange-400 to-sky-400"></div>
                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <span class="grid h-11 w-11 place-items-center rounded-2xl border border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">@include('icons.chart-03')</span>
                                    <span @class(['inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-black', 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' => $active, 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300' => $upcoming, 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' => !$active && !$upcoming])>
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $active ? 'در حال برگزاری' : ($upcoming ? 'به‌زودی' : 'پایان‌یافته') }}
                                    </span>
                                </div>
                                <h3 class="mt-5 line-clamp-2 text-base font-black leading-8 text-slate-900 transition group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-300">
                                    <a href="{{ route('polls.show', $poll->slug) }}" class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">{{ $poll->title }}</a>
                                </h3>
                                <p class="mt-2 line-clamp-3 min-h-[72px] text-xs leading-6 text-slate-500 dark:text-slate-400">{{ $excerpt }}</p>
                                <div class="mt-5 flex flex-wrap gap-2 text-[10px] font-bold text-slate-500 dark:text-slate-400">
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 dark:bg-slate-800">{{ $poll->audience->label() }}</span>
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 dark:bg-slate-800">{{ number_format($poll->active_options_count) }} گزینه</span>
                                    @if($poll->allows_multiple_selections)<span class="rounded-lg bg-violet-50 px-2.5 py-1.5 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300">چندگزینه‌ای</span>@endif
                                </div>
                                <div class="mt-6 flex items-center justify-between gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
                                    <div class="text-[11px] font-bold text-slate-400">
                                        @if($upcoming && $poll->starts_at)
                                            شروع: {{ jalali_date($poll->starts_at, 'Y/m/d') }}
                                        @elseif($active && $poll->ends_at)
                                            تا {{ jalali_date($poll->ends_at, 'Y/m/d') }}
                                        @elseif($poll->show_response_count)
                                            {{ number_format($poll->responses_count) }} پاسخ
                                        @else
                                            بدون محدودیت زمانی
                                        @endif
                                    </div>
                                    <a href="{{ route('polls.show', $poll->slug) }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-black text-brand-600 transition hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 dark:text-brand-300 dark:hover:text-brand-200">
                                        {{ $active ? 'شرکت در نظرسنجی' : 'مشاهده' }}
                                        <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="mt-5 rounded-[24px] border border-dashed border-slate-300 bg-white px-5 py-12 text-center dark:border-slate-700 dark:bg-slate-900 sm:py-16">
                    <span class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 [&_svg]:h-7 [&_svg]:w-7">@include('icons.search-01')</span>
                    <h3 class="mt-5 text-base font-black text-slate-900 dark:text-white">نظرسنجی‌ای پیدا نشد</h3>
                    <p class="mt-2 text-xs leading-7 text-slate-500 dark:text-slate-400">عبارت دیگری جست‌وجو کنید یا همه نظرسنجی‌ها را ببینید.</p>
                    <a href="{{ route('polls.index') }}#poll-list" class="mt-5 inline-flex min-h-10 items-center rounded-xl bg-brand-500 px-4 text-xs font-black text-white transition hover:bg-brand-600">نمایش همه نظرسنجی‌ها</a>
                </div>
            @endif

            @if($polls->hasPages())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900"><x-front.list.pagination :model="$polls"/></div>
            @endif
        </section>
    </main>
@endsection
