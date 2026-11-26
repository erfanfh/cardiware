@php
    // Active user VIP: Menu looks normal; only subtle gold accents distinguish it
    // (Crown, gold active/hover item. Animated stroke on «subscription box VIP» below.
    $isVipUser = $user->hasActiveVipSubscription();
@endphp

<x-front.cart
    @class([
        'hidden space-y-5 text-sm lg:block [&_button]:cursor-pointer [&_svg]:h-[18px] [&_svg]:w-[18px]',
        'border-slate-200 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90' => ! $isVipUser,
        'mw-vip-sidebar relative border-slate-200 bg-white/90 dark:border-slate-800 dark:bg-slate-900/90' => $isVipUser,
    ])>
    @if($isVipUser)
        <span class="pointer-events-none absolute left-3 top-2.5 text-[11px] opacity-70" aria-hidden="true">👑</span>
    @endif
    @php
        $groups = [
            [
                'title' => 'Overview',
                'links' => [
                    ['route' => 'account.dashboard', 'label' => 'Dashboard', 'icon' => 'icons.home-12', 'end' => 'arrow'],
                    ['route' => 'account.notifications.index', 'label' => 'My notifications', 'icon' => 'icons.notification-01', 'end' => 'arrow', 'unreadBadge' => $unreadNotificationCount],
                ],
            ],
            [
                'title' => 'Shopping and downloads',
                'links' => [
                    ['route' => 'account.downloads', 'label' => 'My downloads', 'icon' => 'icons.cloud-download', 'end' => $user->purchase_accesses_count],
                    ['route' => 'account.orders', 'label' => 'My orders', 'icon' => 'icons.shopping-basket-03', 'end' => $user->paid_orders_count],
                    ['route' => 'account.favorites', 'label' => 'Favorites', 'icon' => 'icons.favourite', 'end' => $user->favorite_products_count],
                    ['route' => 'account.transactions', 'label' => 'Transactions', 'icon' => 'icons.transaction', 'end' => 'arrow'],
                ],
            ],
            [
                'title' => 'Support and club',
                'links' => [
                    ['route' => 'account.tickets', 'label' => 'Tickets', 'icon' => 'icons.message-search-02', 'end' => $user->tickets_count],
                    ['route' => 'account.raffles', 'label' => 'My raffles', 'icon' => 'icons.award-03', 'end' => 'arrow'],
                    // Points intentionally hidden here; large numbers break the menu layout (shown on club page)
                    ['route' => 'account.loyalty', 'label' => 'Community Club', 'icon' => 'icons.medal-06', 'end' => 'arrow'],
                    ['route' => 'account.affiliate', 'label' => 'Affiliate program', 'icon' => 'icons.user-multiple-02', 'end' => 'arrow'],
                ],
            ],
            [
                'title' => 'Account',
                'links' => [
                    ['route' => 'account.information', 'label' => 'Account information', 'icon' => 'icons.user-edit-01', 'end' => 'arrow'],
                    ['route' => 'account.security', 'label' => 'Security settings', 'icon' => 'icons.lock-password', 'end' => 'arrow'],
                ],
            ],
        ];

        if ($hasSellerStores) {
            $groups[3]['links'][] = ['route' => 'seller.dashboard', 'label' => 'Seller dashboard', 'icon' => 'icons.store-01', 'end' => 'arrow'];
        }
    @endphp

    @foreach($groups as $group)
        <div class="space-y-1.5">
            <div
                class="px-2 text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                {{ $group['title'] }}
            </div>

            @foreach($group['links'] as $link)
                @php
                    $isActive = request()->routeIs($link['route']);
                    $endValue = $link['end'];
                    $showCount = $endValue !== 'arrow' && (int) $endValue > 0;
                @endphp

                <a
                    href="{{ route($link['route']) }}"
                    class="group relative flex w-full items-center justify-between rounded-2xl border px-4 py-2.5 font-extrabold transition
                    @if($isActive)
                        border-brand-500/30 bg-gradient-to-l from-brand-500/15 to-brand-500/5 text-slate-900 shadow-[0_8px_24px_-12px_rgba(249,115,22,.5)] dark:text-white
                    @else
                        border-transparent text-slate-600 hover:border-slate-200 hover:bg-slate-50 dark:text-slate-300 dark:hover:border-slate-700 dark:hover:bg-slate-800/70
                    @endif"
                >
                    @if($isActive)
                        <span
                            class="absolute inset-y-2 left-0 w-1 rounded-full bg-gradient-to-b from-brand-400 to-brand-600"></span>
                    @endif

                    <span class="flex items-center gap-2.5">
                        <span
                            class="@if($isActive) text-brand-600 dark:text-brand-300 @else text-slate-400 transition group-hover:text-brand-500 dark:text-slate-500 dark:group-hover:text-brand-300 @endif">
                            @include($link['icon'])
                        </span>
                        {{ $link['label'] }}
                    </span>

                    <span class="[&_svg]:!h-4 [&_svg]:!w-4">
                        @if(($link['unreadBadge'] ?? 0) > 0)
                            <span
                                class="mw-badge-glow inline-flex min-w-6 items-center justify-center rounded-full bg-brand-500 px-1.5 py-0.5 text-[11px] font-black text-white"
                                aria-label="{{ $link['unreadBadge'] }} Unread notifications">{{ $link['unreadBadge'] > 99 ? '99+' : $link['unreadBadge'] }}</span>
                        @elseif($showCount)
                            <span
                                class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-1.5 py-0.5 text-[11px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-400">{{ $endValue > 99 ? '99+' : $endValue }}</span>
                        @elseif($endValue === 'arrow')
                            <span
                                class="text-slate-300 transition group-hover:text-brand-400 dark:text-slate-600 dark:group-hover:text-brand-300">
                                <x-front.arrow-right-02/>
                            </span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    @endforeach

    {{-- Logout footer: full width, attached to card bottom to avoid dead space --}}
    <div class="-mx-5 -mb-5 mt-4 rounded-b-2xl border-t border-slate-100 bg-slate-50/70 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/40">
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
                Sign out
            </span>

            <span class="text-rose-700 dark:text-rose-300 [&_svg]:!h-4 [&_svg]:!w-4">
                <x-front.arrow-right-02/>
            </span>
        </button>
    </div>
</x-front.cart>

<x-front.modal
    name="logout-confirm-modal"
    title="Confirm sign-out"
    description="Final confirmation is required to sign out."
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
                    Are you sure you want to sign out?
                </h3>

                <p class="mt-2 text-xs leading-6 text-slate-600 dark:text-slate-400">
                    After signing out, sign in again to see your orders and purchased downloads.
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
                Back to account
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

                    Yes, sign out
                </button>
            </form>
        </div>
    </x-slot>
</x-front.modal>
