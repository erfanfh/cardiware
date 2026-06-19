@extends('front.account.layout')

@section('page-content')
    <section
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">
                    تیکت‌های پشتیبانی
                </div>

                <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                    ارتباط مستقیم با تیم پشتیبانی مودینگ وی
                </div>
            </div>

            <span
                class="inline-flex h-11 w-full cursor-not-allowed items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-4 text-sm font-extrabold text-slate-400 sm:w-auto dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                ثبت تیکت جدید
            </span>
        </div>

        <div
            class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-5 text-center sm:mt-5 sm:rounded-3xl sm:p-8 dark:border-slate-700 dark:bg-slate-800/50">
            <div
                class="mx-auto grid h-14 w-14 place-items-center rounded-3xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100 sm:h-16 sm:w-16 dark:bg-slate-900 dark:text-brand-300 dark:ring-brand-500/20">
                <svg class="h-7 w-7 sm:h-8 sm:w-8" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="mt-4 text-sm font-black leading-7 text-slate-900 sm:text-base dark:text-white">
                سیستم تیکت پشتیبانی به‌زودی اضافه خواهد شد
            </div>

            <p class="mx-auto mt-2 max-w-xl text-xs font-medium leading-6 text-slate-500 sm:text-sm sm:leading-7 dark:text-slate-400">
                به‌زودی می‌توانید درخواست‌های پشتیبانی، مشکلات نصب، بازیابی لینک دانلود و پیگیری سفارش‌ها را مستقیم از
                همین بخش ثبت و مدیریت کنید.
            </p>

            <div class="mt-5 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap sm:items-center sm:justify-center">
                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    ثبت درخواست پشتیبانی
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    مشاهده گفتگوها
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] font-extrabold text-slate-600 dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    پیگیری وضعیت تیکت
                </span>
            </div>
        </div>
    </section>
@endsection
