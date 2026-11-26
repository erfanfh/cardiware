@extends('front.master')

@section('head')
    <title>قرعه کشی ها | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-6 sm:py-8">
        <section class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 shadow-2xl shadow-slate-950/10 dark:border-slate-800 dark:shadow-black/30">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(249,115,22,.22),transparent_28%),radial-gradient(circle_at_85%_15%,rgba(59,130,246,.16),transparent_25%),radial-gradient(circle_at_75%_85%,rgba(168,85,247,.14),transparent_30%)]"></div>
            <div class="absolute -left-20 top-1/2 h-56 w-56 -translate-y-1/2 rounded-full bg-brand-500/10 blur-3xl"></div>
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-orange-500/10 blur-3xl"></div>

            <div class="relative p-5 sm:p-8 lg:p-10">
                <x-front.breadcrumbs
                    nav-class="flex flex-wrap items-center gap-2 text-[11px] font-bold text-white/55 sm:text-xs"
                    link-class="transition hover:text-brand-300"
                    current-class="text-white/90"
                />

                <div class="mt-6 grid gap-7 lg:grid-cols-[minmax(0,1fr)_430px] lg:items-end">
                    <div class="max-w-2xl">
                        <span class="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/5 px-3 py-2 text-[11px] font-extrabold text-white/80 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-brand-400 shadow-[0_0_18px_rgba(249,115,22,.8)]"></span>
                            شانس های واقعی، نتیجه های شفاف
                        </span>

                        <h1 class="mt-4 text-2xl font-black leading-tight text-white sm:text-3xl lg:text-4xl">
                            قرعه کشی های Cardiware
                        </h1>

                        <p class="mt-4 max-w-xl text-sm leading-7 text-slate-300 sm:text-base sm:leading-8">
                            قرعه کشی های فعال و آینده را ببین، جوایز را بررسی کن و بلیط هایی که برای هر کمپین داری یکجا دنبال کن.
                            نتیجه قرعه کشی های تکمیل شده هم در صورت انتشار عمومی قابل مشاهده است.
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @auth
                                <a href="{{ route('account.raffles') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white transition hover:bg-brand-400">
                                    بلیط های من
                                    <span class="[&_svg]:h-4 [&_svg]:w-4">@include('icons.arrow-left-02')</span>
                                </a>
                            @else
                                <a href="{{ route('auth.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white transition hover:bg-brand-400">
                                    ورود به حساب
                                </a>
                            @endauth

                            <a href="#campaigns" class="inline-flex h-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-extrabold text-white/85 backdrop-blur transition hover:bg-white/10">
                                مشاهده قرعه کشی ها
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-2">
                        @foreach([
                            ['label' => 'در حال برگزاری', 'value' => $stats['open_campaigns']],
                            ['label' => 'به زودی', 'value' => $stats['upcoming_campaigns']],
                            ['label' => 'تکمیل شده', 'value' => $stats['completed_campaigns']],
                            ['label' => 'بلیط فعال', 'value' => $stats['active_tickets']],
                        ] as $stat)
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur-md">
                                <div class="text-[10px] font-extrabold text-white/55 sm:text-[11px]">{{ $stat['label'] }}</div>
                                <div class="mt-2 text-xl font-black text-white sm:text-2xl">{{ number_format($stat['value']) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- متن راهنمای کسب امتیاز برای قرعه‌کشی --}}
    <div class="mb-5 flex items-center gap-3 rounded-2xl border border-brand-500/25 bg-brand-500/5 px-4 py-3.5">
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-brand-500/10 text-lg">💡</span>
        <p class="text-xs font-bold leading-6 text-brand-700 dark:text-brand-300">
            با کسب امتیاز از فعالیت‌های روزانه Cardiware — بازدید، دیدگاه، ارسال رسانه و خرید — شانس خود را برای بردن جوایز قرعه‌کشی افزایش دهید.
            <a href="{{ route('account.loyalty') }}#ways-to-earn" class="font-black text-brand-600 hover:text-brand-700 dark:text-brand-400">راهنمای کسب امتیاز ←</a>
        </p>
    </div>


        <section id="campaigns" class="mt-6 scroll-mt-24">
            <x-front.cart class="!p-3 sm:!p-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white sm:text-lg">همه قرعه کشی ها</h2>
                        <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">براساس وضعیت، کمپین مورد نظرت را سریع تر پیدا کن.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap">
                        @foreach([
                            'all' => 'همه',
                            'open' => 'در حال برگزاری',
                            'upcoming' => 'به زودی',
                            'finished' => 'پایان یافته',
                        ] as $key => $label)
                            <a
                                href="{{ route('raffles.index', $key === 'all' ? [] : ['status' => $key]) }}#campaigns"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border px-3 text-xs font-extrabold transition
                                {{ $filter === $key
                                    ? 'border-brand-500 bg-brand-500 text-white shadow-sm'
                                    : 'border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-brand-500/30 dark:hover:bg-brand-500/10 dark:hover:text-brand-300' }}"
                            >
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </x-front.cart>

            @if($campaigns->isNotEmpty())
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($campaigns as $campaign)
                        <x-front.raffle.campaign-card :campaign="$campaign"/>
                    @endforeach
                </div>
            @else
                <div class="mt-4 rounded-3xl border border-dashed border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-12">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500 [&_svg]:h-7 [&_svg]:w-7">
                        @include('icons.award-03')
                    </div>
                    <h3 class="mt-4 text-sm font-black text-slate-800 dark:text-slate-200">قرعه کشی ای در این بخش نیست</h3>
                    <p class="mx-auto mt-2 max-w-md text-xs leading-6 text-slate-500 dark:text-slate-400">
                        وضعیت دیگری را انتخاب کن یا بعدا دوباره این صفحه را بررسی کن.
                    </p>
                </div>
            @endif

            @if($campaigns->hasPages())
                <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <x-front.list.pagination :model="$campaigns"/>
                </div>
            @endif
        </section>
    </main>
@endsection
