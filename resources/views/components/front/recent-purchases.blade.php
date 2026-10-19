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
        <div class="pointer-events-auto flex items-start gap-3 rounded-2xl border border-emerald-200 bg-white p-4 shadow-xl dark:border-emerald-900 dark:bg-slate-900">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 [&_svg]:h-5 [&_svg]:w-5" aria-hidden="true">
                @include('icons.shopping-bag-03')
            </span>
            <div class="min-w-0 flex-1 text-xs leading-6">
                <p class="text-slate-600 dark:text-slate-300">
                    <b data-purchase-buyer class="font-bold text-slate-900 dark:text-white"></b>
                    محصول
                    <a data-purchase-product class="font-bold text-emerald-700 underline-offset-4 hover:underline dark:text-emerald-400"></a>
                    را خریداری کرد.
                </p>
                <p data-purchase-time class="mt-1 text-[11px] text-slate-400"></p>
            </div>
            <button data-purchase-close type="button" class="grid h-7 w-7 shrink-0 cursor-pointer place-items-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white [&_svg]:h-4 [&_svg]:w-4" aria-label="بستن اعلان خریدهای اخیر">
                @include('icons.cancel-01')
            </button>
        </div>
    </div>
@endif
