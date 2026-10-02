@extends('front.master')

@section('head')
    <title>پنل فروشندگان | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-8" dir="rtl">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white">پنل فروشندگان</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">فروشگاهی را که می‌خواهید مدیریت کنید انتخاب کنید.</p>
                </div>
                <a href="{{ route('account.dashboard') }}" class="text-sm font-bold text-brand-600 hover:underline dark:text-brand-300">بازگشت به حساب کاربری</a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @foreach($stores as $store)
                    <a href="{{ route('seller.stores.dashboard', $store) }}" class="rounded-2xl border border-slate-200 p-5 transition hover:border-brand-300 hover:bg-brand-50 dark:border-slate-700 dark:hover:border-brand-500 dark:hover:bg-slate-800">
                        <div class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $store->title }}</div>
                        <div class="mt-3 text-sm font-bold text-brand-600 dark:text-brand-300">ورود به پنل فروشگاه ←</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
