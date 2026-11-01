@php
    $level = $user->loyalty_level ?? null;
@endphp

<x-front.cart class="relative overflow-hidden border-slate-200 bg-white/90 !p-0 dark:border-slate-800 dark:bg-slate-900/90">
    <div
        class="relative bg-gradient-to-l from-slate-950 via-slate-900 to-brand-950/60 p-5 sm:p-6">
        <div class="mw-grid-overlay"></div>
        <div
            class="pointer-events-none absolute -top-16 left-1/4 h-48 w-48 rounded-full bg-brand-500/20 blur-3xl"></div>
        <div
            class="pointer-events-none absolute -bottom-20 right-10 h-44 w-44 rounded-full bg-sky-500/10 blur-3xl"></div>
        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-brand-500/60 to-transparent"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start">
                <div class="relative mx-auto shrink-0 sm:mx-0">
                    <div
                        class="rounded-[1.4rem] bg-gradient-to-tr from-brand-400 via-brand-500 to-amber-400 p-[3px] shadow-[0_0_28px_-6px_rgba(249,115,22,.7)]">
                        <img
                            src="{{ $user->avatar_url ?? asset('img/user-avatar.png') }}"
                            alt="آواتار کاربر"
                            class="h-20 w-20 rounded-[1.25rem] object-cover sm:h-24 sm:w-24"
                        />
                    </div>

                    <a href="{{ route('account.information') }}#account-avatar"
                       aria-label="ویرایش تصویر پروفایل" title="ویرایش تصویر پروفایل"
                       class="absolute -top-2 -right-2 inline-flex size-9 items-center justify-center rounded-xl border-2 border-slate-950 bg-brand-500 text-white shadow-md transition hover:bg-brand-400 focus:outline-none focus:ring-4 focus:ring-brand-300/50 [&_svg]:size-4">
                        @include('icons.pencil-edit-02')
                    </a>

                    <span
                        class="absolute -bottom-1.5 -left-1.5 inline-flex items-center gap-1.5 rounded-xl border border-emerald-400/30 bg-slate-950/90 px-2 py-1 text-[10px] font-extrabold text-emerald-400 backdrop-blur sm:text-[11px]">
                        <span class="mw-pulse-dot !h-1.5 !w-1.5"></span>
                        آنلاین
                    </span>
                </div>

                <div class="min-w-0 text-center sm:text-right">
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <h1 class="flex items-center gap-1.5 text-lg font-black text-white sm:text-2xl [&_svg]:h-5 [&_svg]:w-5 [&_svg]:text-amber-300 sm:[&_svg]:h-6 sm:[&_svg]:w-6">
                            <span class="truncate">
                                سلام {{ "$user->first_name $user->last_name" }}
                            </span>
                            @include('icons.waving-hand-02')
                        </h1>

                        @if($user->hasActiveVipSubscription())
                            <span
                                class="rounded-xl border border-amber-400/40 bg-amber-400/15 px-2 py-1 text-[10px] font-extrabold text-amber-300 sm:text-[11px]">
                                ★ کاربر VIP
                            </span>
                        @endif

                        @if($level)
                            @php
                                $heroLevelColors = $level->colorClasses()
                            @endphp
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/10 py-1 pl-3 pr-1 text-[10px] font-extrabold text-white/90 backdrop-blur sm:text-[11px]">
                                <span
                                    class="grid h-6 w-6 place-items-center rounded-full bg-gradient-to-br {{ $heroLevelColors['ring'] }} text-xs shadow-sm ring-2 ring-white/60">
                                    <span class="drop-shadow-sm">{{ $level->badge }}</span>
                                </span>
                                سطح {{ $level->title }}
                            </span>
                        @endif
                    </div>

                    <p class="mx-auto mt-2 max-w-2xl text-xs leading-6 text-slate-300/90 sm:mx-0 sm:text-sm sm:leading-7">
                        از این بخش می‌تونی سفارش‌ها، دانلودها، اطلاعات حساب، تیکت‌ها و تنظیمات امنیتی خودت رو مدیریت کنی.
                    </p>

                    <div class="mt-4 grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                        <a
                            href="{{ route('account.information') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-extrabold text-white backdrop-blur transition hover:border-white/30 hover:bg-white/15"
                        >
                            ویرایش پروفایل
                        </a>

                        <a
                            href="{{ route('account.downloads') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-b from-brand-400 to-brand-600 px-4 py-2.5 text-sm font-extrabold text-white shadow-[0_10px_28px_-10px_rgba(249,115,22,.8)] transition hover:from-brand-300 hover:to-brand-500"
                        >
                            دانلودهای من
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3 xl:w-[420px]">
                @foreach([
                    ['label' => 'دانلود فعال', 'value' => $user->purchase_accesses_count],
                    ['label' => 'سفارش‌ها', 'value' => $user->paid_orders_count],
                    ['label' => 'تیکت باز', 'value' => $user->open_tickets_count],
                    ['label' => 'امتیاز', 'value' => $user->loyalty_points],
                ] as $stat)
                    <div
                        class="rounded-2xl border border-white/10 bg-white/[.07] p-3 text-center backdrop-blur transition hover:border-brand-400/30 hover:bg-white/10 sm:rounded-3xl sm:p-4">
                        <div class="text-[10px] font-extrabold text-slate-400 sm:text-[11px]">
                            {{ $stat['label'] }}
                        </div>
                        <div class="mt-1.5 text-lg font-black text-white sm:mt-2 sm:text-2xl">
                            {{ $stat['value'] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-front.cart>
