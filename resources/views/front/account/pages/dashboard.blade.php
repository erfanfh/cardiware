@extends('front.account.layout')

@section('page-content')
    <div class="flex min-w-0 flex-col gap-4 sm:gap-6">
        <section
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4 xl:gap-6 [&_svg]:h-5 [&_svg]:w-5 sm:[&_svg]:h-6 sm:[&_svg]:w-6">

            @php
                $cards = [
                    [
                        'route' => 'account.downloads',
                        'border' => 'emerald',
                        'icon' => 'icons.cloud-download',
                        'badge' => 'Ready to claim',
                        'title' => 'Available downloads',
                        'desc' => 'Downloadable files',
                        'value' => $user->purchase_accesses_count,
                        'action' => 'View',
                    ],
                    [
                        'route' => 'account.orders',
                        'border' => 'brand',
                        'icon' => 'icons.shopping-basket-03',
                        'badge' => 'Successful payment',
                        'title' => 'Completed orders',
                        'desc' => 'Successful store orders',
                        'value' => $user->paid_orders_count,
                        'action' => 'View',
                    ],
                    [
                        'route' => 'account.tickets',
                        'border' => 'cyan',
                        'icon' => 'icons.message-search-02',
                        'badge' => 'Needs follow-up',
                        'title' => 'Active tickets',
                        'desc' => 'Awaiting response or follow-up',
                        'value' => $user->open_tickets_count,
                        'action' => 'Follow up',
                    ],
                    [
                        'route' => 'account.favorites',
                        'border' => 'rose',
                        'icon' => 'icons.favourite',
                        'badge' => 'Saved',
                        'title' => 'Favorites',
                        'desc' => 'Products saved for later',
                        'value' => $user->favorite_products_count,
                        'action' => 'View',
                    ],
                ];
            @endphp

            @foreach($cards as $card)
                <a
                    href="{{ route($card['route']) }}"
                    class="group relative block overflow-hidden rounded-2xl border bg-white p-4 text-start shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-soft sm:rounded-3xl sm:p-5 dark:bg-slate-900/90 dark:shadow-black/20
                    @if($card['border'] === 'emerald') border-emerald-100 dark:border-emerald-500/20
                    @elseif($card['border'] === 'brand') border-brand-100 dark:border-brand-500/20
                    @elseif($card['border'] === 'cyan') border-cyan-100 dark:border-cyan-500/20
                    @else border-rose-100 dark:border-rose-500/20
                    @endif"
                >
                    <div class="pointer-events-none absolute -left-8 -top-8 h-20 w-20 rounded-full blur-2xl transition sm:h-24 sm:w-24
                        @if($card['border'] === 'emerald') bg-emerald-100/70 group-hover:bg-emerald-200/80 dark:bg-emerald-500/10 dark:group-hover:bg-emerald-500/15
                        @elseif($card['border'] === 'brand') bg-brand-100/70 group-hover:bg-brand-200/80 dark:bg-brand-500/10 dark:group-hover:bg-brand-500/15
                        @elseif($card['border'] === 'cyan') bg-cyan-100/70 group-hover:bg-cyan-200/80 dark:bg-cyan-500/10 dark:group-hover:bg-cyan-500/15
                        @else bg-rose-100/70 group-hover:bg-rose-200/80 dark:bg-rose-500/10 dark:group-hover:bg-rose-500/15
                        @endif"
                    ></div>

                    <div class="relative flex items-start justify-between gap-3">
                        <div class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border sm:h-11 sm:w-11
                            @if($card['border'] === 'emerald') border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300
                            @elseif($card['border'] === 'brand') border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300
                            @elseif($card['border'] === 'cyan') border-cyan-100 bg-cyan-50 text-cyan-600 dark:border-cyan-500/20 dark:bg-cyan-500/10 dark:text-cyan-300
                            @else border-rose-100 bg-rose-50 text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300
                            @endif"
                        >
                            @include($card['icon'])
                        </div>

                        <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold sm:text-[11px]
                            @if($card['border'] === 'emerald') bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300
                            @elseif($card['border'] === 'brand') bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300
                            @elseif($card['border'] === 'cyan') bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-300
                            @else bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300
                            @endif"
                        >
                            {{ $card['badge'] }}
                        </span>
                    </div>

                    <div class="relative mt-4 sm:mt-5">
                        <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                            {{ $card['title'] }}
                        </div>

                        <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            {{ $card['desc'] }}
                        </div>

                        <div class="mt-4 flex items-end justify-between gap-3 sm:mt-5">
                            <div class="text-2xl font-black text-slate-900 sm:text-3xl dark:text-white">
                                {{ $card['value'] }}
                            </div>

                            <div class="flex items-center gap-1 text-xs font-extrabold transition [&_svg]:!h-[14px] [&_svg]:!w-[14px]
                                @if($card['border'] === 'emerald') text-emerald-600 dark:text-emerald-300
                                @elseif($card['border'] === 'brand') text-brand-600 dark:text-brand-300
                                @elseif($card['border'] === 'cyan') text-cyan-600 dark:text-cyan-300
                                @else text-rose-600 dark:text-rose-300
                                @endif"
                            >
                                {{ $card['action'] }}
                                <x-front.arrow-right-02/>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </section>

        @include('front.account.partials.dashboard-loyalty-card', ['audienceClub' => $audienceClub])

        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-6">
            <div
                class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">
                            Yesterday’s most viewed
                        </div>

                        <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            Posts viewed most yesterday
                        </div>
                    </div>
                </div>

                <div class="mt-4 space-y-3 sm:mt-5">
                    @forelse($yesterdayTopPosts as $view)
                        @php($post = $view->pageable)

                        @if($post)
                            <a
                                href="{{ route('contents.detail', $post->slug) }}"
                                class="group block rounded-2xl border border-slate-200 bg-slate-50 p-3 transition hover:bg-white hover:shadow-sm sm:p-4 dark:border-slate-700 dark:bg-slate-800/70 dark:hover:bg-slate-800"
                            >
                                <div class="flex items-start justify-between gap-3 sm:gap-4">
                                    <div class="min-w-0">
                                        <div
                                            class="line-clamp-1 text-sm font-extrabold text-slate-900 transition group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">
                                            {{ $post->title }}
                                        </div>

                                        <div
                                            class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                            {{ $post->excerpt ?? 'One of the most viewed posts Cardiware yesterday' }}
                                        </div>
                                    </div>

                                    <div
                                        class="shrink-0 rounded-2xl border border-slate-200 bg-white px-2.5 py-2 text-center sm:px-3 dark:border-slate-700 dark:bg-slate-900">
                                        <div class="text-xs font-black text-slate-900 sm:text-sm dark:text-white">
                                            {{ number_format($view->count) }}
                                        </div>

                                        <div class="mt-0.5 text-[10px] font-bold text-slate-400 dark:text-slate-500">
                                            views
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endif
                    @empty
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-white p-5 text-center text-xs text-slate-500 sm:rounded-3xl dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-400">
                            No posts found!
                        </div>
                    @endforelse
                </div>
            </div>

            <div
                class="min-w-0 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white">
                    Quick shortcuts
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:mt-5 sm:grid-cols-2">
                    @foreach([
                        ['route' => 'account.downloads', 'title' => 'Go to downloads', 'desc' => 'View purchased files'],
                        ['route' => 'account.orders', 'title' => 'Manage orders', 'desc' => 'Invoices and order details'],
                        ['route' => 'account.tickets', 'title' => 'Support', 'desc' => 'Create or track tickets'],
                        ['route' => 'account.security', 'title' => 'Account security', 'desc' => 'Password and verification'],
                    ] as $shortcut)
                        <a
                            href="{{ route($shortcut['route']) }}"
                            class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 text-start transition hover:bg-white hover:shadow-sm dark:border-slate-700 dark:bg-slate-800/70 dark:hover:bg-slate-800"
                        >
                            <div class="text-sm font-extrabold text-slate-900 dark:text-white">
                                {{ $shortcut['title'] }}
                            </div>

                            <div class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                {{ $shortcut['desc'] }}
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
