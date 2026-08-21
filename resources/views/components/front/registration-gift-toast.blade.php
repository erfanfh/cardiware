@php($gift = session('loyalty_registration_gift'))

@if(is_array($gift))
    <div
        x-data="{ open: false, copied: false }"
        x-init="open = true; setTimeout(() => open = false, 12000)"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-5 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-3 opacity-0"
        x-cloak
        class="fixed inset-x-3 bottom-4 z-[100001] mx-auto w-auto max-w-[28rem] sm:inset-x-auto sm:bottom-6 sm:left-6 sm:mx-0 sm:w-[28rem]"
        role="status"
        aria-live="polite"
        dir="rtl"
    >
        <div class="relative overflow-hidden rounded-[1.75rem] border border-emerald-200/80 bg-white/95 p-5 shadow-2xl shadow-emerald-950/20 backdrop-blur-xl dark:border-emerald-400/20 dark:bg-slate-900/95">
            <div class="pointer-events-none absolute -left-12 -top-16 h-40 w-40 rounded-full bg-emerald-300/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 right-4 h-36 w-36 rounded-full bg-amber-300/20 blur-3xl"></div>

            <div class="relative flex items-start gap-3.5">
                <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white shadow-lg shadow-emerald-500/25 [&_svg]:h-7 [&_svg]:w-7">
                    @include('icons.user-star-01')
                </div>

                <div class="min-w-0 flex-1">
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/20">
                        هدیه عضویت
                    </span>
                    <h2 class="mt-2 text-base font-black leading-7 text-slate-950 dark:text-white">
                        خوش اومدی! 🎉
                    </h2>
                    <p class="mt-1 text-xs font-semibold leading-6 text-slate-600 dark:text-slate-300">
                        پاداش «{{ $gift['title'] ?? 'هدیه ثبت‌نام' }}» همین الان برای حسابت فعال شد.
                    </p>
                </div>

                <button
                    type="button"
                    x-on:click="open = false"
                    class="grid h-8 w-8 shrink-0 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white [&_svg]:h-4 [&_svg]:w-4"
                    aria-label="بستن پیام هدیه"
                >
                    @include('icons.cancel-01')
                </button>
            </div>

            @if(filled($gift['coupon_code'] ?? null))
                <div class="relative mt-4 flex items-center justify-between gap-3 rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/80 px-3.5 py-3 dark:border-emerald-400/20 dark:bg-emerald-400/10">
                    <div>
                        <div class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300">کد تخفیف اختصاصی تو</div>
                        <code class="mt-1 block text-sm font-black tracking-wide text-slate-900 dark:text-white" dir="ltr">{{ $gift['coupon_code'] }}</code>
                    </div>
                    <button
                        type="button"
                        x-on:click="navigator.clipboard.writeText(@js($gift['coupon_code'])); copied = true; setTimeout(() => copied = false, 1800)"
                        class="rounded-xl bg-white px-3 py-2 text-[11px] font-black text-emerald-700 shadow-sm dark:bg-slate-800 dark:text-emerald-300"
                    >
                        <span x-show="!copied">کپی کد</span>
                        <span x-show="copied" x-cloak>کپی شد ✓</span>
                    </button>
                </div>
            @endif

            <a href="{{ route('account.loyalty') }}" class="relative mt-4 inline-flex text-xs font-black text-brand-600 dark:text-brand-300">
                مشاهده جزئیات در باشگاه
            </a>
        </div>
    </div>
@endif
