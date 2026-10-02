@extends('front.master')

@section('head')
    <title>پنل فروشگاه {{ $store->title }} | {{ get_setting('general', 'title') }}</title>
@endsection

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-8" dir="rtl">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <a href="{{ route('seller.dashboard') }}" class="text-sm font-bold text-brand-600 hover:underline dark:text-brand-300">بازگشت به پنل فروشندگان</a>
            <h1 class="mt-5 text-2xl font-black text-slate-900 dark:text-white">پنل فروشگاه {{ $store->title }}</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600 dark:text-slate-300">دسترسی فروشندگان این فروشگاه فعال است. ابزارهای مدیریت فروشگاه در مراحل بعد به این صفحه اضافه می‌شوند.</p>
        </div>
    </section>
@endsection
