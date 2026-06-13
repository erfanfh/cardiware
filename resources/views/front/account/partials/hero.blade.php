<x-front.cart class="relative overflow-hidden">
    <div class="absolute -top-10 -left-10 h-28 w-28 rounded-[48px] bg-brand-100/70 blur-[1px]"></div>
    <div class="absolute top-4 left-6 h-3 w-3 rounded-full bg-brand-500"></div>

    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
            <div class="relative shrink-0">
                <img
                    src="{{ $user->avatar?->url ?? asset('img/user-avatar.png') }}"
                    alt="آواتار کاربر"
                    class="h-20 w-20 sm:h-24 sm:w-24 rounded-3xl object-cover ring-4 ring-brand-100 shadow-sm"
                />
                <span
                    class="absolute -bottom-1 -left-1 inline-flex items-center gap-1 rounded-xl bg-emerald-50 border border-emerald-200 px-2 py-1 text-[11px] font-extrabold text-emerald-700">
                    آنلاین
                </span>
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center [&_svg]:w-6 [&_svg]:h-6 [&_svg]:text-yellow-600 gap-1.5">
                        <span>
                            سلام
                            {{ "$user->first_name $user->last_name" }}
                        </span>
                        @include('icons.waving-hand-02')
                    </h1>
                    @if(false)
                        <span
                            class="text-[11px] font-extrabold text-emerald-800 bg-emerald-50 border border-emerald-100 px-2 py-1 rounded-xl">
                            کاربر VIP
                        </span>
                    @else
                        <span
                            class="text-[11px] font-extrabold text-amber-800 bg-amber-50 border border-amber-100 px-2 py-1 rounded-xl">
                            VIP نیستی
                        </span>
                    @endif
                </div>

                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">
                    از این بخش می‌تونی سفارش‌ها، دانلودها، اطلاعات حساب، تیکت‌ها و تنظیمات امنیتی خودت رو مدیریت
                    کنی.
                </p>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a
                        href="{{ route('account.information') }}"
                        class="cursor-pointer inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-extrabold text-brand-700 hover:bg-brand-50 hover:border-brand-200 transition"
                    >
                        ویرایش پروفایل
                    </a>

                    <a
                        href="{{ route('account.downloads') }}"
                        class="cursor-pointer inline-flex items-center gap-2 rounded-2xl bg-brand-500 px-4 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-brand-600 transition"
                    >
                        دانلودهای من
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:w-[420px]">
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center">
                <div class="text-[11px] font-extrabold text-slate-500">دانلود فعال</div>
                <div
                    class="mt-2 text-xl sm:text-2xl font-black text-slate-900">{{ $user->purchase_accesses_count }}</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center">
                <div class="text-[11px] font-extrabold text-slate-500">سفارش‌ها</div>
                <div class="mt-2 text-xl sm:text-2xl font-black text-slate-900">{{ $user->paid_orders_count }}</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center">
                <div class="text-[11px] font-extrabold text-slate-500">تیکت باز</div>
                <div class="mt-2 text-xl sm:text-2xl font-black text-slate-900">0</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4 text-center">
                <div class="text-[11px] font-extrabold text-slate-500">امتیاز</div>
                <div class="mt-2 text-xl sm:text-2xl font-black text-slate-900">0</div>
            </div>
        </div>
    </div>
</x-front.cart>
