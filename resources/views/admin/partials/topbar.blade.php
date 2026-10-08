<div x-data="adminTopbar()" class="admin-topbar sticky top-0 z-20">
    <header
        class="h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center justify-between px-5 py-[10px] relative top-0 z-20 ps-[12px] pe-[14px] lg:px-5"
    >
        <div class="flex items-center gap-3 min-w-0">
            <button
                type="button"
                class="inline-flex lg:hidden items-center justify-center h-10 w-10 rounded-2xl bg-slate-100 text-slate-700 cursor-pointer border border-slate-200 transition hover:bg-brand-50 hover:text-brand-600"
                aria-label="باز کردن منو"
                aria-controls="Sidebar"
                aria-expanded="false"
                id="SidebarToggle"
            >
                <svg viewBox="0 0 24 24" width="20" height="20">
                    <path d="M4 7h16M4 12h16M4 17h16"
                          stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
            </button>

            <div class="flex flex-col min-w-0">
                <h1 class="text-[1rem] font-bold text-black truncate">
                    @yield('title')
                </h1>

                @hasSection('topbar_subtitle')
                    <p class="text-xs text-slate-500 truncate">
                        @yield('topbar_subtitle')
                    </p>
                @endif
            </div>
        </div>

        <div class="admin-topbar-actions flex shrink-0 items-center gap-2 sm:gap-3 relative">

            <a href="{{ route('home') }}" target="_blank"
               class="hidden sm:inline-flex items-center gap-1.5 px-3 h-10 rounded-2xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                @include('icons.link-square-02')

                مشاهده سایت
            </a>

            <!-- Notifications -->
            <div class="relative">
                <button
                    type="button"
                    @click="toggleNotifications()"
                    aria-label="اعلان‌ها"
                    class="cursor-pointer [&_svg]:w-[18px] [&_svg]:h-[18px] relative inline-flex items-center justify-center h-10 w-10 rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50"
                >
                    @include('icons.notification-01')

                    @if($adminHeaderUnreadCount > 0)
                        <span class="absolute -top-1 -right-1 rounded-full bg-brand-500 px-1.5 py-[1px] text-[10px] font-bold text-white" aria-label="{{ $adminHeaderUnreadCount }} اعلان خوانده‌نشده">{{ $adminHeaderUnreadCount > 99 ? '99+' : $adminHeaderUnreadCount }}</span>
                    @endif
                </button>

                <!-- Dropdown -->
                <div
                    x-cloak
                    x-show="notificationsOpen"
                    @click.outside="notificationsOpen = false"
                    x-transition
                    class="absolute left-0 top-full mt-2 w-80 max-w-[calc(100vw-1rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl z-50"
                >
                    <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900">
                        اعلان‌ها @if($adminHeaderUnreadCount > 0)<span class="mr-1 text-xs text-brand-600">{{ $adminHeaderUnreadCount }} خوانده‌نشده</span>@endif
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        @forelse($adminHeaderNotifications as $notification)
                            <form method="post" action="{{ route('account.notifications.read', $notification) }}" class="border-b border-slate-100 last:border-b-0">
                                @csrf
                                <button type="submit" class="w-full cursor-pointer px-4 py-3 text-right transition hover:bg-slate-50 {{ $notification->read_at ? '' : 'bg-brand-50/60' }}">
                                    <span class="block text-sm font-semibold text-slate-800">{{ $notification->title }}</span>
                                    @if($notification->body)<span class="mt-1 block text-xs leading-5 text-slate-500">{{ $notification->body }}</span>@endif
                                    <span class="mt-1 block text-[11px] text-slate-400">{{ jalali_date($notification->sent_at, 'Y/m/d H:i') }}</span>
                                </button>
                            </form>
                        @empty
                            <p class="px-4 py-8 text-center text-xs text-slate-500">در حال حاضر اعلانی برای شما وجود ندارد.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('account.notifications.index') }}" class="block cursor-pointer border-t border-slate-100 px-4 py-3 text-center text-xs font-bold text-brand-600 transition hover:bg-slate-50">مشاهده همه اعلان‌ها</a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="relative" @keydown.escape.window="closeUserMenu()">
                <button
                    @click="toggleUserMenu()"
                    aria-label="منوی حساب کاربری"
                    :aria-expanded="userMenuOpen.toString()"
                    class="admin-user-toggle cursor-pointer flex items-center gap-2 ps-[5px] pe-[12px] py-[5px] rounded-full border border-slate-200 transition hover:bg-slate-50"
                >
                    <div
                        class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-500 to-orange-500 flex items-center justify-center text-white text-sm font-semibold">
                        {{ mb_substr(auth()->user()->display_name, 0, 1) }}
                    </div>

                    <div class="admin-user-summary flex flex-col text-right">
                        <div class="text-xs font-semibold">
                            {{ auth()->user()->display_name }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            {{ auth()->user()->role->title }}
                        </div>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4 text-slate-400 transition"
                         :class="{ 'rotate-180': userMenuOpen }"
                         viewBox="0 0 24 24" fill="none">
                        <path d="m6 9l6 6l6-6"
                              stroke="currentColor"
                              stroke-width="1.6"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </button>

                <div
                    x-cloak
                    x-show="userMenuOpen"
                    @click.outside="closeUserMenu()"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
                    class="absolute left-0 top-full mt-2 min-w-56 overflow-hidden rounded-3xl border border-slate-200 bg-white p-2"
                >
                    <div class="rounded-2xl bg-slate-100/80 px-3 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-orange-500 text-sm font-black text-white">
                                {{ mb_substr(auth()->user()->display_name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate text-sm font-black text-slate-900">
                                    {{ auth()->user()->display_name }}
                                </div>

                                <div class="truncate text-xs font-medium text-slate-500">
                                    {{ auth()->user()->email }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 space-y-1">
                        <a
                            href="{{ route('account.dashboard') }}"
                            class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-[0.8rem] font-semibold text-slate-700 transition hover:bg-slate-100/80 hover:text-brand-600"
                        >
                            @include('icons.user')
                            حساب کاربری
                        </a>

                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="cursor-pointer flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-[0.8rem] font-semibold text-red-600 transition hover:bg-red-50 hover:text-red-700"
                            >
                                @include('icons.logout-square-01')
                                خروج از حساب
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- Overlay -->
    <div
        x-cloak
        x-show="userMenuOpen || notificationsOpen"
        x-transition.opacity
        @click="userMenuOpen = false; notificationsOpen = false"
        class="fixed inset-0 z-10 bg-slate-900/20 backdrop-blur-[2px]"
    ></div>
</div>

@push('scripts')
    <script>
        function adminTopbar() {
            return {
                userMenuOpen: false,
                notificationsOpen: false,

                toggleUserMenu() {
                    this.userMenuOpen = !this.userMenuOpen;
                    this.notificationsOpen = false;
                },

                closeUserMenu() {
                    this.userMenuOpen = false;
                },

                toggleNotifications() {
                    this.notificationsOpen = !this.notificationsOpen;
                    this.userMenuOpen = false;
                }
            };
        }
    </script>
@endpush
