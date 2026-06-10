@extends('front.account.layout')

@section('page-content')
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-base font-extrabold text-slate-900">علاقه‌مندی‌ها</div>
                <div class="mt-1 text-xs text-slate-500">محصولاتی که برای بعد ذخیره می‌کنی اینجا نمایش داده
                    می‌شوند
                </div>
            </div>

            <span
                class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                ۰ مورد
            </span>
        </div>

        <div class="mt-5 rounded-3xl border border-dashed border-slate-300 bg-slate-50/60 p-8 text-center">
            <div
                class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="mt-4 text-base font-black text-slate-900">
                هنوز محصولی به علاقه‌مندی‌ها اضافه نشده است
            </div>

            <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-7 text-slate-500">
                وقتی محصولی را دوست داشتی، با زدن دکمه علاقه‌مندی آن را ذخیره کن تا بعدا سریع‌تر به آن دسترسی داشته
                باشی.
            </p>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    ذخیره محصولات برای بعد
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    دسترسی سریع از داشبورد
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    مدیریت لیست علاقه‌مندی‌ها
                </span>
            </div>

            <div class="mt-6">
                <a href="{{ route('products.list') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-brand-500 px-5 py-2.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600">
                    مشاهده محصولات
                </a>
            </div>
        </div>
    </section>
@endsection
