@extends('front.master')

@section('head')
    <title>پنل فروشندگان | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:py-12" dir="rtl">
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 p-7 text-white shadow-xl shadow-slate-900/10 sm:p-10 dark:bg-slate-800">
            <div class="pointer-events-none absolute -left-16 -top-24 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-6">
                <div class="min-w-0"><div class="text-xs font-bold text-brand-300">فضای کاری فروشندگان</div><h1 class="mt-2 text-3xl font-black">پنل فروشندگان</h1><p class="mt-3 text-sm leading-7 text-slate-300">فروشگاهتان را انتخاب کنید و گزارش فروش، محصولات و گفتگوهای مشتریان را ببینید.</p></div>
                <a href="{{ route('account.dashboard') }}" class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-xs font-bold hover:bg-white/20">بازگشت به حساب کاربری</a>
            </div>
        </div>
        <div class="mt-7 flex items-center justify-between"><h2 class="text-lg font-black">فروشگاه‌های شما</h2><span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-black text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ number_format($stores->count()) }} فروشگاه</span></div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($stores as $store)
                <a href="{{ route('seller.stores.dashboard', $store) }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg hover:shadow-brand-600/10 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-brand-500">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 9h18l-1.5-5h-15L3 9Zm1 0v11h16V9M9 20v-7h6v7M3 9c0 2 3 3 4.5 1.5C9 12 12 12 12 9c0 3 3 3 4.5 1.5C18 12 21 11 21 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                    <h3 class="mt-5 break-words text-lg font-black">{{ $store->title }}</h3>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">گزارش فروش، محصولات، دیدگاه‌ها و تیکت‌ها</p>
                    <div class="mt-6 text-xs font-black text-brand-600 group-hover:translate-x-[-3px] dark:text-brand-300">ورود به پنل ←</div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
