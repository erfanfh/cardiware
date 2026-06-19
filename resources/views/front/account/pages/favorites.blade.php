@extends('front.account.layout')

@section('page-content')
    <section
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">علاقه‌مندی‌ها</div>
                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    محصولاتی که برای بعد ذخیره می‌کنی اینجا نمایش داده می‌شوند
                </div>
            </div>

            <span
                class="inline-flex w-fit rounded-xl border border-slate-200 bg-slate-100 px-2.5 py-1 text-[11px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                ۰ مورد
            </span>
        </div>

        <div
            class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-5 text-center sm:mt-5 sm:rounded-3xl sm:p-8 dark:border-slate-700 dark:bg-slate-800/50">
            <div
                class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100 sm:h-16 sm:w-16 dark:bg-slate-900 dark:text-brand-300 dark:ring-brand-500/20">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="mt-4 text-sm font-black leading-7 text-slate-900 sm:text-base dark:text-white">
                هنوز محصولی به علاقه‌مندی‌ها اضافه نشده است
            </div>

            <p class="mx-auto mt-2 max-w-xl text-xs font-medium leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                وقتی محصولی را دوست داشتی، با زدن دکمه علاقه‌مندی آن را ذخیره کن تا بعداً سریع‌تر به آن دسترسی داشته
                باشی.
            </p>

            <div class="mt-5 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap sm:items-center sm:justify-center">
                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    ذخیره محصولات برای بعد
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    دسترسی سریع از داشبورد
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    مدیریت لیست علاقه‌مندی‌ها
                </span>
            </div>

            <div class="mt-6">
                <a
                    href="{{ route('products.list') }}"
                    class="inline-flex h-11 w-full items-center justify-center rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 dark:hover:bg-brand-400 sm:w-auto"
                >
                    مشاهده محصولات
                </a>
            </div>
        </div>
    </section>
@endsection
