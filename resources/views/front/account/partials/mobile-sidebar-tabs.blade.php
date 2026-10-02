{{-- Mobile Account Navigation --}}
<div class="lg:hidden">
    <x-front.cart
        class="relative overflow-hidden border-slate-200 bg-white/90 p-3 dark:border-slate-800 dark:bg-slate-900/90">
        <div
            class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-full bg-brand-100/70 blur-2xl dark:bg-brand-500/10"></div>

        <div class="relative mb-3 flex items-center justify-between gap-3">
            <div>
                <div class="text-sm font-black text-slate-900 dark:text-white">منوی حساب کاربری</div>
                <div class="mt-1 text-[11px] font-bold text-slate-500 dark:text-slate-400">دسترسی سریع به بخش‌ها</div>
            </div>

            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.user-edit-01')
            </span>
        </div>

        <div class="relative grid grid-cols-3 sm:grid-cols-5 gap-2 text-center">
            @php
                $items = [
                    ['route' => 'account.dashboard', 'label' => 'داشبورد', 'icon' => 'icons.home-12'],
                    ['route' => 'account.notifications.index', 'label' => 'اعلان‌ها', 'icon' => 'icons.notification-01', 'badge' => $unreadNotificationCount > 0 ? ($unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount) : null],
                    ['route' => 'account.downloads', 'label' => 'دانلودها', 'icon' => 'icons.cloud-download', 'badge' => $user->purchase_accesses_count, 'badgeColor' => 'emerald'],
                    ['route' => 'account.orders', 'label' => 'سفارش‌ها', 'icon' => 'icons.shopping-basket-03', 'badge' => $user->paid_orders_count, 'badgeColor' => 'brand'],
                    ['route' => 'account.tickets', 'label' => 'تیکت‌ها', 'icon' => 'icons.message-search-02', 'badge' => $user->open_tickets_count, 'badgeColor' => 'cyan'],
                    ['route' => 'account.favorites', 'label' => 'علاقه‌مندی', 'icon' => 'icons.favourite', 'badge' => $user->favorite_products_count, 'badgeColor' => 'rose'],
                    ['route' => 'account.raffles', 'label' => 'قرعه کشی', 'icon' => 'icons.award-03'],
                    ['route' => 'account.transactions', 'label' => 'تراکنش‌ها', 'icon' => 'icons.transaction'],
                    ['route' => 'account.loyalty', 'label' => 'باشگاه', 'icon' => 'icons.medal-06'],
                    ['route' => 'account.information', 'label' => 'اطلاعات', 'icon' => 'icons.user-edit-01'],
                    ['route' => 'account.security', 'label' => 'امنیت', 'icon' => 'icons.lock-password'],
                ];
            @endphp

            @foreach($items as $item)
                @php($isActive = request()->routeIs($item['route']))

                <a
                    href="{{ route($item['route']) }}"
                    class="group relative flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
                    @if($isActive)
                        border-brand-200 bg-brand-500 text-white shadow-soft dark:border-brand-400/30 dark:bg-brand-500
                    @else
                        border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700/70
                    @endif"
                >
                    @isset($item['badge'])
                        <span class="absolute left-2 top-2 rounded-full px-1.5 py-0.5 text-[10px] font-black
                            @if(($item['badgeColor'] ?? '') === 'emerald')
                                bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300
                            @elseif(($item['badgeColor'] ?? '') === 'cyan')
                                bg-cyan-50 text-cyan-600 dark:bg-cyan-500/10 dark:text-cyan-300
                            @elseif(($item['badgeColor'] ?? '') === 'rose')
                                bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300
                            @else
                                bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-300
                            @endif
                        ">
                            {{ $item['badge'] }}
                        </span>
                    @endisset

                    <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                        @if($isActive)
                            bg-white/20 text-white
                        @else
                            bg-slate-50 text-brand-500 group-hover:bg-brand-50 dark:bg-slate-900/70 dark:text-brand-300 dark:group-hover:bg-brand-500/10
                        @endif"
                    >
                        @include($item['icon'])
                    </span>

                    <span class="text-[11px] font-extrabold">{{ $item['label'] }}</span>
                </a>
            @endforeach

            <button
                type="button"
                x-data
                @click="$dispatch('open-modal', 'logout-confirm-modal')"
                class="group flex min-h-[76px] cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50 px-2 py-3 text-rose-600 transition hover:bg-rose-100 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/15"
            >
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-2xl bg-white text-rose-600 dark:bg-slate-900/70 dark:text-rose-300 [&_svg]:h-5 [&_svg]:w-5">
                    @include('icons.logout-square-01')
                </span>
                <span class="text-[11px] font-extrabold">خروج</span>
            </button>
        </div>
    </x-front.cart>
</div>
