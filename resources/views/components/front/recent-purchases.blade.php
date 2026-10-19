@if(app(\App\Infrastructure\Services\Setting\Groups\NotificationsSettings::class)->recentPurchasesEnabled() && !request()->routeIs('preview.*', 'auth.*', 'account.*', 'seller.*', 'cart.*', 'payment.*', 'launcher.checkout.*', 'launcher.purchase-complete'))
    <div
        data-recent-purchases
        data-url="{{ route('notifications.recent-purchases') }}"
        class="pointer-events-none fixed inset-x-3 bottom-4 z-[80] mx-auto max-w-sm sm:inset-x-auto sm:bottom-6 sm:right-6 sm:mx-0 sm:w-96"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        hidden
    >
        <div class="recent-purchase-card pointer-events-auto relative overflow-hidden rounded-3xl border border-emerald-200/80 bg-white/95 p-4 shadow-[0_16px_50px_-12px_rgba(5,150,105,0.28)] backdrop-blur-xl dark:border-emerald-400/20 dark:bg-slate-900/95 dark:shadow-[0_16px_50px_-12px_rgba(0,0,0,0.5)]">
            <div class="pointer-events-none absolute -right-10 -top-12 h-36 w-36 rounded-full bg-emerald-300/20 blur-2xl dark:bg-emerald-400/10" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-l from-transparent via-emerald-400/70 to-transparent" aria-hidden="true"></div>
            <div class="relative flex items-start gap-3">
                <span class="recent-purchase-icon relative grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 text-white shadow-lg shadow-emerald-500/20 [&_svg]:h-6 [&_svg]:w-6" aria-hidden="true">
                    @include('icons.shopping-bag-03')
                    <span class="absolute -bottom-1 -left-1 grid h-5 w-5 place-items-center rounded-full bg-white text-emerald-600 ring-2 ring-white dark:bg-slate-900 dark:text-emerald-400 dark:ring-slate-900 [&_svg]:h-4 [&_svg]:w-4">
                        @include('icons.checkmark-badge-01')
                    </span>
                </span>
                <div class="min-w-0 flex-1 text-xs leading-6">
                    <span class="mb-1 inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        خرید موفق
                    </span>
                    <p class="text-slate-600 dark:text-slate-300">
                        <b data-purchase-buyer class="font-bold text-slate-900 dark:text-white"></b>
                        محصول
                        <a data-purchase-product class="font-bold text-emerald-700 underline-offset-4 hover:underline dark:text-emerald-400"></a>
                        را خریداری کرد.
                    </p>
                    <p data-purchase-time class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400"></p>
                </div>
                <button data-purchase-close type="button" class="grid h-7 w-7 shrink-0 cursor-pointer place-items-center rounded-full text-slate-400 transition-colors hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-500 dark:hover:bg-emerald-400/10 dark:hover:text-emerald-400 [&_svg]:h-4 [&_svg]:w-4" aria-label="بستن اعلان خریدهای اخیر">
                    @include('icons.cancel-01')
                </button>
            </div>
        </div>
    </div>
@endif
