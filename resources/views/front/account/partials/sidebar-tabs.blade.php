<x-front.cart class="hidden lg:block space-y-2 text-sm [&_button]:cursor-pointer [&_svg]:w-[18px] [&_svg]:h-[18px]">
    <button
        type="button"
        @click="setTab('dashboard')"
        :class="activeTab === 'dashboard'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-extrabold transition"
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
    </button>

    <button
        type="button"
        @click="setTab('downloads')"
        :class="activeTab === 'downloads'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.cloud-download')
            </span>
            دانلودهای من
        </span>
        <span class="text-slate-400">0</span>
    </button>

    <button
        type="button"
        @click="setTab('orders')"
        :class="activeTab === 'orders'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.shopping-basket-03')
            </span>
            سفارش‌های من
        </span>
        <span class="text-slate-400">{{ $user->paid_orders_count }}</span>
    </button>

    <button
        type="button"
        @click="setTab('profile')"
        :class="activeTab === 'profile'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
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
    </button>

    <button
        type="button"
        @click="setTab('tickets')"
        :class="activeTab === 'tickets'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.message-search-02')
            </span>
            تیکت‌ها
        </span>
        <span class="text-slate-400">0</span>
    </button>

    <button
        type="button"
        @click="setTab('favorites')"
        :class="activeTab === 'favorites'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
    >
        <span class="flex items-center gap-2">
            <span class="text-brand-500">
                @include('icons.favourite')
            </span>
            علاقه‌مندی‌ها
        </span>
        <span class="text-slate-400">0</span>
    </button>

    <button
        type="button"
        @click="setTab('security')"
        :class="activeTab === 'security'
                ? 'border-brand-100 bg-brand-50 text-slate-900'
                : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'"
        class="w-full flex items-center justify-between rounded-2xl border px-4 py-3 font-bold transition"
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
    </button>

    <button
        type="button"
        @click="setTab('logout')"
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
