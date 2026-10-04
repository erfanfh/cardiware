@extends('front.master')

@section('head')
    <title>@yield('seller-title', 'پنل فروشگاه') | {{ $store->title }} | {{ get_setting('general', 'title') }}</title>
    <meta name="robots" content="noindex,nofollow">
@endsection

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 lg:py-10" dir="rtl">
        <div class="relative overflow-hidden rounded-[2rem] bg-slate-900 px-6 py-7 text-white shadow-xl shadow-slate-900/10 sm:px-9 sm:py-9 dark:bg-slate-800">
            <div class="pointer-events-none absolute -left-12 -top-20 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-0 right-1/3 h-28 w-72 rounded-full bg-sky-500/10 blur-3xl"></div>
            <div class="relative flex flex-wrap items-center justify-between gap-5">
                <div class="min-w-0">
                    <div class="break-words text-xs font-bold tracking-wide text-brand-300">پنل فروشندگان / {{ $store->title }}</div>
                    <h1 class="mt-2 break-words text-2xl font-black sm:text-3xl">@yield('seller-heading', $store->title)</h1>
                    <p class="mt-2 break-words text-sm leading-7 text-slate-300">@yield('seller-description', 'مدیریت فروش، دیدگاه‌ها و گفتگوهای فروشگاه')</p>
                </div>
                <a href="{{ route('seller.dashboard') }}" class="rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-xs font-bold transition hover:bg-white/20">تغییر فروشگاه</a>
            </div>
        </div>

        <nav aria-label="بخش‌های پنل فروشگاه" class="mt-5 flex gap-2 overflow-x-auto pb-2">
            @foreach([
                ['route' => 'seller.stores.dashboard', 'label' => 'نمای کلی'],
                ['route' => 'seller.stores.products', 'label' => 'محصولات'],
                ['route' => 'seller.stores.withdrawals', 'label' => 'درخواست وجه'],
                ['route' => 'seller.stores.comments', 'label' => 'دیدگاه‌ها'],
                ['route' => 'seller.stores.tickets', 'label' => 'تیکت‌ها'],
            ] as $tab)
                @php($active = request()->routeIs($tab['route']) || ($tab['route'] === 'seller.stores.products' && request()->routeIs('seller.stores.products.*')) || ($tab['route'] === 'seller.stores.tickets' && request()->routeIs('seller.stores.tickets.*')))
                <a href="{{ route($tab['route'], $store) }}" @if($active) aria-current="page" @endif class="inline-flex shrink-0 items-center gap-2 rounded-xl px-5 py-3 text-sm font-extrabold transition {{ $active ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-brand-200 hover:text-brand-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300' }}">
                    {{ $tab['label'] }}
                    @if($tab['route'] === 'seller.stores.tickets')
                        <span class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] font-black {{ $sellerUnansweredTicketCount > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200' : ($active ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}" aria-label="{{ number_format($sellerUnansweredTicketCount) }} تیکت نیازمند پاسخ">{{ number_format($sellerUnansweredTicketCount) }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="mt-5">@yield('seller-content')</div>
    </main>
@endsection
