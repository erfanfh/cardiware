<x-front.cart class="relative overflow-hidden">
    <div class="absolute -top-10 -left-10 h-24 w-24 rounded-[42px] bg-brand-100/70 blur-[1px] sm:h-28 sm:w-28"></div>
    <div class="absolute top-4 left-6 h-2.5 w-2.5 rounded-full bg-brand-500 sm:h-3 sm:w-3"></div>

    <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
            <div class="relative mx-auto shrink-0 sm:mx-0">
                <img
                    src="{{ $user->avatar?->url ?? asset('img/user-avatar.png') }}"
                    alt="آواتار کاربر"
                    class="h-20 w-20 rounded-3xl object-cover shadow-sm ring-4 ring-brand-100 sm:h-24 sm:w-24"
                />

                <span
                    class="absolute -bottom-1 -left-1 inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700 sm:text-[11px]">
                    آنلاین
                </span>
            </div>

            <div class="min-w-0 text-center sm:text-right">
                <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                    <h1 class="flex items-center gap-1.5 text-lg font-black text-slate-900 sm:text-2xl [&_svg]:h-5 [&_svg]:w-5 [&_svg]:text-yellow-600 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                        <span class="truncate">
                            سلام {{ "$user->first_name $user->last_name" }}
                        </span>
                        @include('icons.waving-hand-02')
                    </h1>

                    @if(false)
                        <span
                            class="rounded-xl border border-emerald-100 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-800 sm:text-[11px]">
                            کاربر VIP
                        </span>
                    @else
                        <span
                            class="rounded-xl border border-amber-100 bg-amber-50 px-2 py-1 text-[10px] font-extrabold text-amber-800 sm:text-[11px]">
                            VIP نیستی
                        </span>
                    @endif
                </div>

                <p class="mx-auto mt-2 max-w-2xl text-xs leading-6 text-slate-600 sm:mx-0 sm:text-sm sm:leading-7">
                    از این بخش می‌تونی سفارش‌ها، دانلودها، اطلاعات حساب، تیکت‌ها و تنظیمات امنیتی خودت رو مدیریت کنی.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                    <a
                        href="{{ route('account.information') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50"
                    >
                        ویرایش پروفایل
                    </a>

                    <a
                        href="{{ route('account.downloads') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600"
                    >
                        دانلودهای من
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3 xl:w-[420px]">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-center sm:rounded-3xl sm:p-4">
                <div class="text-[10px] font-extrabold text-slate-500 sm:text-[11px]">دانلود فعال</div>
                <div
                    class="mt-1.5 text-lg font-black text-slate-900 sm:mt-2 sm:text-2xl">{{ $user->purchase_accesses_count }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-center sm:rounded-3xl sm:p-4">
                <div class="text-[10px] font-extrabold text-slate-500 sm:text-[11px]">سفارش‌ها</div>
                <div
                    class="mt-1.5 text-lg font-black text-slate-900 sm:mt-2 sm:text-2xl">{{ $user->paid_orders_count }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-center sm:rounded-3xl sm:p-4">
                <div class="text-[10px] font-extrabold text-slate-500 sm:text-[11px]">تیکت باز</div>
                <div class="mt-1.5 text-lg font-black text-slate-900 sm:mt-2 sm:text-2xl">0</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3 text-center sm:rounded-3xl sm:p-4">
                <div class="text-[10px] font-extrabold text-slate-500 sm:text-[11px]">امتیاز</div>
                <div class="mt-1.5 text-lg font-black text-slate-900 sm:mt-2 sm:text-2xl">0</div>
            </div>
        </div>
    </div>
</x-front.cart>
