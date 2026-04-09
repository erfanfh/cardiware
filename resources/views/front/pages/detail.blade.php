@extends('front.master')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8">
        @include('front.pages.partials.detail.title')

        <section class="grid grid-cols-1 lg:grid-cols-10 gap-6">
            <div class="lg:col-span-7 space-y-6">
                @include('front.pages.partials.detail.content')
            </div>

            <aside class="lg:col-span-3 space-y-6">
                @include('front.layouts.sidebar-widgets')

                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-5">
                    <div class="font-extrabold text-slate-700">جایگاه تبلیغات</div>
                    <div class="mt-2 text-sm text-slate-500">بنر یا باکس اسپانسر (۳۰۰×۲۵۰)</div>
                    <div class="mt-1 text-xs text-slate-400">در نسخه نهایی می‌توان اسلات/کامپوننت کرد.</div>
                </div>
            </aside>
        </section>
    </main>
@endsection
