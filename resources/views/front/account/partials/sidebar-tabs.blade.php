<x-front.cart
    class="hidden space-y-2 border-slate-200 bg-white/90 text-sm dark:border-slate-800 dark:bg-slate-900/90 lg:block [&_button]:cursor-pointer [&_svg]:h-[18px] [&_svg]:w-[18px]">
    @php
        $links = [
            ['route' => 'account.dashboard', 'label' => 'داشبورد', 'icon' => 'icons.home-12', 'end' => 'arrow'],
            ['route' => 'account.notifications.index', 'label' => 'اعلان‌های من', 'icon' => 'icons.notification-01', 'end' => 'arrow', 'unreadBadge' => $unreadNotificationCount],
            ['route' => 'account.downloads', 'label' => 'دانلودهای من', 'icon' => 'icons.cloud-download', 'end' => $user->purchase_accesses_count],
            ['route' => 'account.orders', 'label' => 'سفارش‌های من', 'icon' => 'icons.shopping-basket-03', 'end' => $user->paid_orders_count],
            ['route' => 'account.tickets', 'label' => 'تیکت‌ها', 'icon' => 'icons.message-search-02', 'end' => $user->tickets_count],
            ['route' => 'account.favorites', 'label' => 'علاقه‌مندی‌ها', 'icon' => 'icons.favourite', 'end' => $user->favorite_products_count],
            ['route' => 'account.raffles', 'label' => 'قرعه کشی‌های من', 'icon' => 'icons.award-03', 'end' => 'arrow'],
            ['route' => 'account.transactions', 'label' => 'تراکنش‌ها', 'icon' => 'icons.transaction', 'end' => 'arrow'],
            ['route' => 'account.loyalty', 'label' => 'باشگاه مخاطبان', 'icon' => 'icons.medal-06', 'end' => $user->loyalty_points],
            ['route' => 'account.information', 'label' => 'اطلاعات حساب', 'icon' => 'icons.user-edit-01', 'end' => 'arrow'],
            ['route' => 'account.security', 'label' => 'تنظیمات امنیت', 'icon' => 'icons.lock-password', 'end' => 'arrow'],
        ];
    @endphp

    @foreach($links as $link)
        @php($isActive = request()->routeIs($link['route']))

        <a
            href="{{ route($link['route']) }}"
            class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition
            @if($isActive)
                border-brand-100 bg-brand-50 text-slate-900 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-white
            @else
                border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/70
            @endif"
        >
            <span class="flex items-center gap-2">
                <span class="text-brand-500 dark:text-brand-300">
                    @include($link['icon'])
                </span>
                {{ $link['label'] }}
            </span>

            <span class="text-slate-400 dark:text-slate-500 [&_svg]:!h-4 [&_svg]:!w-4">
                @if(($link['unreadBadge'] ?? 0) > 0)
                    <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-brand-500 px-1.5 py-0.5 text-[11px] font-black text-white" aria-label="{{ $link['unreadBadge'] }} اعلان خوانده‌نشده">{{ $link['unreadBadge'] > 99 ? '99+' : $link['unreadBadge'] }}</span>
                @elseif($link['end'] === 'arrow')
                    @include('icons.arrow-left-02')
                @else
                    {{ $link['end'] }}
                @endif
            </span>
        </a>
    @endforeach

    <button
        type="button"
        x-data
        @click="$dispatch('open-modal', 'logout-confirm-modal')"
        class="flex w-full items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 font-extrabold text-rose-700 transition hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/15"
    >
        <span class="flex items-center gap-2">
            <span class="text-rose-600 dark:text-rose-300">
                @include('icons.logout-square-01')
            </span>
            خروج از حساب
        </span>

        <span class="text-rose-700 dark:text-rose-300 [&_svg]:!h-4 [&_svg]:!w-4">
            @include('icons.arrow-left-02')
        </span>
    </button>
</x-front.cart>

<x-front.modal
    name="logout-confirm-modal"
    title="تأیید خروج از حساب"
    description="برای خروج از حساب کاربری، تأیید نهایی لازم است."
    maxWidth="max-w-lg"
>
    <div class="rounded-3xl border border-rose-100 bg-rose-50 p-5 dark:border-rose-500/20 dark:bg-rose-500/10">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl border border-rose-200 bg-white text-rose-600 shadow-sm dark:border-rose-500/20 dark:bg-slate-900 dark:text-rose-300">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M15.5 8.5V7A3.5 3.5 0 0 0 12 3.5H6.5A3.5 3.5 0 0 0 3 7v10a3.5 3.5 0 0 0 3.5 3.5H12a3.5 3.5 0 0 0 3.5-3.5v-1.5"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 12h10m0 0-3.5-3.5M20 12l-3.5 3.5" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            <div>
                <h3 class="text-sm font-black text-slate-900 dark:text-white">
                    مطمئنی می‌خواهی خارج شوی؟
                </h3>

                <p class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-400">
                    بعد از خروج، برای مشاهده سفارش‌ها و دانلودهای خریداری‌شده باید دوباره وارد حساب کاربری شوی.
                </p>
            </div>
        </div>
    </div>

    <x-slot name="footer">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button
                type="button"
                x-data
                @click="$dispatch('close-modal', 'logout-confirm-modal')"
                class="inline-flex h-11 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
            >
                بازگشت به حساب
            </button>

            <form method="POST" action="{{ route('auth.logout') }}" class="w-full sm:w-auto">
                @csrf

                <button
                    type="submit"
                    class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-500 sm:w-auto"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M15.5 8.5V7A3.5 3.5 0 0 0 12 3.5H6.5A3.5 3.5 0 0 0 3 7v10a3.5 3.5 0 0 0 3.5 3.5H12a3.5 3.5 0 0 0 3.5-3.5v-1.5"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M10 12h10m0 0-3.5-3.5M20 12l-3.5 3.5" stroke="currentColor" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    بله، خارج شو
                </button>
            </form>
        </div>
    </x-slot>
</x-front.modal>
