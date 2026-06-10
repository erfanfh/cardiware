<x-front.cart class="hidden lg:block space-y-2 text-sm [&_button]:cursor-pointer [&_svg]:w-[18px] [&_svg]:h-[18px]">
    <a
        href="{{ route('account.dashboard') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.dashboard')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.home-12')
            </span>
            داشبورد
        </span>
        <span class="text-slate-400 [&_svg]:!w-4 ![&_svg]:!h-4">
            @include('icons.arrow-left-02')
        </span>
    </a>

    <a
        href="{{ route('account.downloads') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.downloads')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.cloud-download')
            </span>
            دانلودهای من
        </span>
        <span class="text-slate-400">{{ $user->purchase_accesses_count }}</span>
    </a>

    <a
        href="{{ route('account.orders') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.orders')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.shopping-basket-03')
            </span>
            سفارش‌های من
        </span>
        <span class="text-slate-400">{{ $user->paid_orders_count }}</span>
    </a>

    <a
        href="{{ route('account.tickets') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.tickets')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.message-search-02')
            </span>
            تیکت‌ها
        </span>
        <span class="text-slate-400">0</span>
    </a>

    <a
        href="{{ route('account.favorites') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.favorites')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.favourite')
            </span>
            علاقه‌مندی‌ها
        </span>
        <span class="text-slate-400">0</span>
    </a>

    <a
        href="{{ route('account.information') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.information')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.user-edit-01')
            </span>
            اطلاعات حساب
        </span>
        <span class="text-slate-400 [&_svg]:!w-4 ![&_svg]:!h-4">
            @include('icons.arrow-left-02')
        </span>
    </a>

    <a
        href="{{ route('account.security') }}"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition @if(request()->routeIs('account.security')) border-brand-100 bg-brand-50 text-slate-900 @else border-slate-200 bg-white text-slate-700 hover:bg-slate-50 @endif"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.lock-password')
            </span>
            تنظیمات امنیت
        </span>
        <span class="text-slate-400 [&_svg]:!w-4 ![&_svg]:!h-4">
            @include('icons.arrow-left-02')
        </span>
    </a>

    <button
        type="button"
        x-data
        @click="$dispatch('open-modal', 'logout-confirm-modal')"
        class="w-full flex items-center justify-between rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 font-extrabold text-rose-700 hover:bg-rose-100 transition"
    >
        <span class="flex items-center gap-2">
            <span class="text-rose-600">
                @include('icons.logout-square-01')
            </span>
            خروج از حساب
        </span>
        <span class="text-rose-700 [&_svg]:!w-4 ![&_svg]:!h-4">
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
    <div class="rounded-3xl border border-rose-100 bg-rose-50 p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
            <div
                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-3xl border border-rose-200 bg-white text-rose-600 shadow-sm"
            >
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M15.5 8.5V7A3.5 3.5 0 0 0 12 3.5H6.5A3.5 3.5 0 0 0 3 7v10a3.5 3.5 0 0 0 3.5 3.5H12a3.5 3.5 0 0 0 3.5-3.5v-1.5"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                    <path
                        d="M10 12h10m0 0-3.5-3.5M20 12l-3.5 3.5"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div>
                <h3 class="text-sm font-black text-slate-900">
                    مطمئنی می‌خواهی خارج شوی؟
                </h3>

                <p class="mt-2 text-xs leading-6 text-slate-600">
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
                class="cursor-pointer inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
            >
                بازگشت به حساب
            </button>

            <form method="POST" action="{{ route('auth.logout') }}" class="w-full sm:w-auto">
                @csrf

                <button
                    type="submit"
                    class="cursor-pointer inline-flex h-11 w-full items-center justify-center gap-2 rounded-2xl bg-rose-600 px-5 text-sm font-extrabold text-white shadow-soft transition hover:bg-rose-700 sm:w-auto"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M15.5 8.5V7A3.5 3.5 0 0 0 12 3.5H6.5A3.5 3.5 0 0 0 3 7v10a3.5 3.5 0 0 0 3.5 3.5H12a3.5 3.5 0 0 0 3.5-3.5v-1.5"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                        <path
                            d="M10 12h10m0 0-3.5-3.5M20 12l-3.5 3.5"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    بله، خارج شو
                </button>
            </form>
        </div>
    </x-slot>
</x-front.modal>
