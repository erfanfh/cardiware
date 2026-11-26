@auth
    <div
        data-loyalty-earned-toast
        data-pending-url="{{ route('loyalty.earned-notifications.index') }}"
        data-pending-cookie="{{ \App\Application\Loyalty\Notifications\PendingLoyaltyEarnedNotifications::CLIENT_COOKIE }}"
        class="pointer-events-none fixed inset-x-3 bottom-4 z-[100000] flex justify-center sm:inset-x-auto sm:bottom-6 sm:left-6 sm:block"
        aria-live="polite"
        aria-atomic="true"
    >
        <article
            data-loyalty-earned-card
            class="loyalty-earned-toast-card pointer-events-auto relative w-full max-w-[26rem] overflow-hidden rounded-[1.75rem] border border-amber-200/80 bg-white/95 shadow-2xl shadow-amber-950/20 backdrop-blur-xl dark:border-amber-400/20 dark:bg-slate-900/95 dark:shadow-black/50 sm:w-[26rem]"
            role="status"
            hidden
        >
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="absolute -right-14 -top-20 h-44 w-44 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-400/15"></div>
                <div class="absolute -bottom-24 -left-12 h-40 w-40 rounded-full bg-brand-400/20 blur-3xl dark:bg-brand-400/10"></div>

                @foreach(range(1, 8) as $particle)
                    <span class="loyalty-earned-particle"></span>
                @endforeach
            </div>

            <div class="relative p-4 sm:p-5" dir="ltr">
                <div class="flex items-start gap-3.5">
                    <div class="loyalty-earned-icon relative grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-amber-300 via-amber-400 to-orange-500 text-white shadow-lg shadow-amber-500/25 [&_svg]:h-7 [&_svg]:w-7">
                        <span class="absolute -left-1 -top-1 grid h-5 w-5 place-items-center rounded-full bg-white text-amber-500 shadow-md dark:bg-slate-800 [&_svg]:h-3 [&_svg]:w-3">
                            @include('icons.star')
                        </span>
                        @include('icons.award-03')
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-400/10 dark:text-amber-300 dark:ring-amber-400/20">
                                Community Club
                            </span>
                        </div>

                        <h2 data-loyalty-earned-title class="text-base font-black leading-7 text-slate-950 dark:text-white"></h2>
                        <p data-loyalty-earned-message class="mt-1 text-xs font-semibold leading-6 text-slate-600 dark:text-slate-300"></p>
                    </div>

                    <button
                        data-loyalty-earned-close
                        type="button"
                        class="grid h-8 w-8 shrink-0 cursor-pointer place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 dark:hover:bg-slate-800 dark:hover:text-white [&_svg]:h-4 [&_svg]:w-4"
                        aria-label="Dismiss points message"
                    >
                        @include('icons.cancel-01')
                    </button>
                </div>

                <div class="mt-4 flex items-center gap-3 rounded-2xl border border-amber-100 bg-gradient-to-l from-amber-50 to-orange-50/50 px-3.5 py-3 dark:border-amber-400/10 dark:from-amber-400/10 dark:to-orange-400/5">
                    <div class="flex min-w-0 flex-1 items-baseline gap-1.5 text-amber-700 dark:text-amber-300">
                        <span class="text-xl font-black" dir="ltr">+<span data-loyalty-earned-points></span></span>
                        <span class="text-xs font-extrabold">points</span>
                    </div>

                    <div class="h-8 w-px bg-amber-200/80 dark:bg-amber-400/15"></div>

                    <div class="min-w-0 text-left" dir="ltr">
                        <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400">New balance</div>
                        <div class="mt-0.5 text-xs font-black text-slate-800 dark:text-slate-100">
                            <span data-loyalty-earned-balance></span> points
                        </div>
                    </div>
                </div>

                <a
                    data-loyalty-earned-club-link
                    href="{{ route('account.loyalty') }}"
                    class="mt-3 inline-flex items-center gap-1 text-xs font-black text-brand-600 transition hover:text-brand-700 focus:outline-none focus-visible:rounded-lg focus-visible:ring-2 focus-visible:ring-brand-400 dark:text-brand-300 dark:hover:text-brand-200 [&_svg]:h-3.5 [&_svg]:w-3.5"
                >
                    View club and rewards
                    <x-front.arrow-right-02/>
                </a>
            </div>

            <div class="absolute inset-x-0 bottom-0 h-1 bg-amber-100/80 dark:bg-slate-800" aria-hidden="true">
                <div data-loyalty-earned-progress class="loyalty-earned-progress h-full origin-right bg-gradient-to-l from-amber-400 to-orange-500"></div>
            </div>
        </article>
    </div>
@endauth
