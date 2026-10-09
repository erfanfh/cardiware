@php
    $personalNotifications = collect($personalHeaderNotifications ?? []);
    $manualNotifications = get_setting('header', 'notifications') ?: [];
    $notifications = $personalNotifications->filter(fn ($notification) => empty($notification['read_at']))
        ->concat($manualNotifications)
        ->concat($raffleHeaderNotifications ?? collect())
        ->concat($personalNotifications->filter(fn ($notification) => !empty($notification['read_at'])))
        ->values()
        ->all();
    $notificationBadgeCount = ($personalUnreadCount ?? 0)
        + count($manualNotifications)
        + count($raffleHeaderNotifications ?? []);
@endphp
<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
>
    <button
        type="button"
        @click="open = !open"
        class="relative cursor-pointer h-10 w-10 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
        aria-label="اعلان‌ها"
    >
        @include('icons.notification-01')

        @if($notificationBadgeCount > 0)
            {{-- Outer pulse halo --}}
            <span
                class="pointer-events-none absolute -top-1 -left-1 h-5 w-5 rounded-full bg-brand-500/35 animate-[notificationPing_1.8s_ease-out_infinite]"
            ></span>

            {{-- Soft glow --}}
            <span
                class="pointer-events-none absolute -top-1 -left-1 h-5 w-5 rounded-full bg-brand-500/30 blur-[6px] animate-pulse"
            ></span>

            {{-- Badge --}}
            <span
                class="absolute -top-1 -left-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-brand-500 text-white text-[10px] font-bold leading-none grid place-items-center border-2 border-white shadow-lg shadow-brand-500/30 dark:border-slate-900 animate-[badgePop_1.8s_ease-in-out_infinite]"
            >
            {{ $notificationBadgeCount > 9 ? '9+' : $notificationBadgeCount }}
        </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute top-full left-0 z-[100] mt-3 w-[24rem] max-w-[calc(100vw-1rem)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl max-sm:right-auto max-sm:left-0 max-sm:mt-2 max-sm:w-[calc(100vw-8rem)] max-sm:max-w-none dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30"
    >
        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">اعلان‌ها</div>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{ count($notifications) }} اعلان
            </div>
        </div>

        <div class="max-h-[24rem] overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $hasLink = !empty($notification['url']);
                    $hasSubtitle = !empty($notification['subtitle']);
                    $isUnread = empty($notification['read_at']);
                @endphp

                @if(!empty($notification['id']))
                    <form method="post" action="{{ route('account.notifications.read', $notification['id']) }}" class="border-b border-slate-100 last:border-b-0 dark:border-slate-800">
                        @csrf
                        <button type="submit" class="group flex w-full cursor-pointer items-start gap-3 px-4 py-4 text-right transition hover:bg-slate-50 dark:hover:bg-slate-800/70 {{ $isUnread ? 'bg-brand-50/50 dark:bg-brand-500/10' : 'bg-slate-50/70 dark:bg-slate-800/30' }}">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border {{ $isUnread ? 'border-brand-100 bg-brand-50 text-brand-600 dark:border-brand-500/20 dark:bg-brand-500/10 dark:text-brand-300' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-500' }}">@include('icons.notification-01')</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm leading-6 {{ $isUnread ? 'font-semibold text-slate-700 dark:text-slate-300' : 'font-medium text-slate-500 dark:text-slate-400' }}">{{ $notification['title'] }}</span>
                                @if($hasSubtitle)<span class="mt-1 block text-[0.7rem] {{ $isUnread ? 'text-slate-500 dark:text-slate-400' : 'text-slate-400 dark:text-slate-500' }}">{{ $notification['subtitle'] }}</span>@endif
                                @if(!$isUnread)<span class="mt-1 block text-[0.65rem] text-slate-400 dark:text-slate-500">خوانده‌شده</span>@endif
                            </span>
                        </button>
                    </form>
                @else
                <a
                    href="{{ $hasLink ? $notification['url'] : '#' }}"
                    @if(!$hasLink) @click.prevent @endif
                    class="group flex {{ $hasSubtitle ? 'items-start' : 'items-center' }} gap-3 border-b border-slate-100 px-4 py-4 transition last:border-b-0
                   bg-white hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:hover:bg-slate-800/70
                   {{ $hasLink ? 'cursor-pointer' : 'cursor-default' }}">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        @include('icons.notification-01')
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold leading-6 text-slate-700 dark:text-slate-300 {{ $hasLink ? 'group-hover:text-slate-900 dark:group-hover:text-slate-100' : '' }}">
                            {{ $notification['title'] }}
                        </p>

                        @if($hasSubtitle)
                            <p class="mt-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                {{ $notification['subtitle'] }}
                            </p>
                        @endif
                    </div>
                </a>
                @endif
            @empty
                <div class="px-4 py-10 text-center">
                    <div
                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
                        @include('icons.notification-01')
                    </div>

                    <div class="mt-4 text-sm font-bold text-slate-700 dark:text-slate-200">
                        اعلانی وجود ندارد
                    </div>

                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        فعلاً اعلان جدیدی برای نمایش ندارید.
                    </p>
                </div>
            @endforelse
        </div>
        @auth
            <a href="{{ route('account.notifications.index') }}" class="block cursor-pointer border-t border-slate-100 px-4 py-3 text-center text-sm font-bold text-brand-600 dark:border-slate-800">مشاهده همه اعلان‌های من</a>
        @endauth
    </div>
</div>
