@extends('front.account.layout')

@section('page-content')
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-base font-extrabold text-slate-900">تیکت‌های پشتیبانی</div>
                <div class="mt-1 text-xs text-slate-500">ارتباط مستقیم با تیم پشتیبانی مودینگ وی</div>
            </div>

            <span
                class="inline-flex cursor-not-allowed items-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-extrabold text-slate-400">
                ثبت تیکت جدید
            </span>
        </div>

        <div class="mt-5 rounded-3xl border border-dashed border-slate-300 bg-slate-50/60 p-8 text-center">
            <div
                class="mx-auto grid h-16 w-16 place-items-center rounded-3xl bg-white text-brand-600 shadow-sm ring-1 ring-brand-100">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M8 10h8M8 14h5M21 12c0 4.418-4.03 8-9 8a10.4 10.4 0 0 1-4.064-.816L3 20l1.26-3.36A7.57 7.57 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"/>
                </svg>
            </div>

            <div class="mt-4 text-base font-black text-slate-900">
                سیستم تیکت پشتیبانی به‌زودی اضافه خواهد شد
            </div>

            <p class="mx-auto mt-2 max-w-xl text-sm font-medium leading-7 text-slate-500">
                به‌زودی می‌توانید درخواست‌های پشتیبانی، مشکلات نصب، بازیابی لینک دانلود و پیگیری سفارش‌ها را مستقیم
                از همین بخش ثبت و مدیریت کنید.
            </p>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    ثبت درخواست پشتیبانی
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    مشاهده گفتگوها
                </span>

                <span
                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-extrabold text-slate-600">
                    پیگیری وضعیت تیکت
                </span>
            </div>
        </div>
    </section>
@endsection
