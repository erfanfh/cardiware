{{-- Mobile Account Navigation --}}
<div class="lg:hidden">
    <x-front.cart class="relative overflow-hidden p-3">
        <div
            class="pointer-events-none absolute -top-10 -left-10 h-28 w-28 rounded-full bg-brand-100/70 blur-2xl"></div>

        <div class="relative mb-3 flex items-center justify-between gap-3">
            <div>
                <div class="text-sm font-black text-slate-900">منوی حساب کاربری</div>
                <div class="mt-1 text-[11px] font-bold text-slate-500">دسترسی سریع به بخش‌ها</div>
            </div>

            <span
                class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 [&_svg]:h-5 [&_svg]:w-5">
                @include('icons.user-edit-01')
            </span>
        </div>

        <div class="relative grid grid-cols-4 gap-2 text-center">
            <a href="{{ route('account.dashboard') }}"
               class="group flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.dashboard')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.dashboard')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.home-12')
                </span>
                <span class="text-[11px] font-extrabold">داشبورد</span>
            </a>

            <a href="{{ route('account.downloads') }}"
               class="group relative flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.downloads')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span
                    class="absolute left-2 top-2 rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-black text-emerald-600">
                    {{ $user->purchase_accesses_count }}
                </span>
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.downloads')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.cloud-download')
                </span>
                <span class="text-[11px] font-extrabold">دانلودها</span>
            </a>

            <a href="{{ route('account.orders') }}"
               class="group relative flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.orders')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span
                    class="absolute left-2 top-2 rounded-full bg-brand-50 px-1.5 py-0.5 text-[10px] font-black text-brand-600">
                    {{ $user->paid_orders_count }}
                </span>
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.orders')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.shopping-basket-03')
                </span>
                <span class="text-[11px] font-extrabold">سفارش‌ها</span>
            </a>

            <a href="{{ route('account.tickets') }}"
               class="group flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.tickets')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.tickets')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.message-search-02')
                </span>
                <span class="text-[11px] font-extrabold">تیکت‌ها</span>
            </a>

            <a href="{{ route('account.favorites') }}"
               class="group flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.favorites')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.favorites')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.favourite')
                </span>
                <span class="text-[11px] font-extrabold">علاقه‌مندی</span>
            </a>

            <a href="{{ route('account.information') }}"
               class="group flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.information')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.information')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.user-edit-01')
                </span>
                <span class="text-[11px] font-extrabold">اطلاعات</span>
            </a>

            <a href="{{ route('account.security') }}"
               class="group flex min-h-[76px] flex-col items-center justify-center gap-2 rounded-2xl border px-2 py-3 transition
               @if(request()->routeIs('account.security')) border-brand-200 bg-brand-500 text-white shadow-soft @else border-slate-200 bg-white text-slate-600 hover:bg-slate-50 @endif">
                <span class="flex h-9 w-9 items-center justify-center rounded-2xl transition [&_svg]:h-5 [&_svg]:w-5
                    @if(request()->routeIs('account.security')) bg-white/20 text-white @else bg-slate-50 text-brand-500 group-hover:bg-brand-50 @endif">
                    @include('icons.lock-password')
                </span>
                <span class="text-[11px] font-extrabold">امنیت</span>
            </a>

            <button
                type="button"
                x-data
                @click="$dispatch('open-modal', 'logout-confirm-modal')"
                class="group flex min-h-[76px] cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border border-rose-100 bg-rose-50 px-2 py-3 text-rose-600 transition hover:bg-rose-100"
            >
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-2xl bg-white text-rose-600 [&_svg]:h-5 [&_svg]:w-5">
                    @include('icons.logout-square-01')
                </span>
                <span class="text-[11px] font-extrabold">خروج</span>
            </button>
        </div>
    </x-front.cart>
</div>
