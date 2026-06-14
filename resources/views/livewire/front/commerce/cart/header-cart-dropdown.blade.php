<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
>
    <button
        type="button"
        x-on:click="
        open = !open;

        if (open) {
            $wire.openDropdown();
        }
    "
        wire:loading.class="opacity-70"
        class="relative cursor-pointer h-10 w-10 [&_svg]:w-[1.125rem] [&_svg]:h-[1.125rem] rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 transition grid place-items-center text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800"
        aria-label="سبد خرید"
    >
        @include('icons.shopping-basket-03')

        @if($summary['items_count'] > 0)
            <span
                class="pointer-events-none absolute -top-1 -left-1 h-5 w-5 rounded-full bg-brand-500/35 animate-[notificationPing_1.8s_ease-out_infinite]"
            ></span>

            <span
                class="pointer-events-none absolute -top-1 -left-1 h-5 w-5 rounded-full bg-brand-500/30 blur-[6px] animate-pulse"
            ></span>

            <span
                class="absolute -top-1 -left-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-brand-500 text-white text-[10px] font-bold leading-none grid place-items-center border-2 border-white shadow-lg shadow-brand-500/30 dark:border-slate-900 animate-[badgePop_1.8s_ease-in-out_infinite]"
            >
                {{ $summary['items_count'] > 9 ? '9+' : $summary['items_count'] }}
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
        class="absolute top-full left-0 z-[100] mt-3 w-[26rem] max-w-[calc(100vw-1rem)] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl max-sm:right-auto max-sm:left-0 max-sm:mt-2 max-sm:w-[calc(100vw-2rem)] max-sm:max-w-none dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30"
    >
        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                        سبد خرید
                    </div>

                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ $summary['items_count'] }} آیتم در سبد خرید شما
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="reloadCart"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-white hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-950 dark:hover:text-slate-100"
                    aria-label="به‌روزرسانی سبد خرید"
                >
                    <svg wire:loading.class="animate-spin" wire:target="reloadCart,toggle"
                         xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-4 w-4" viewBox="0 0 24 24">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="1.5"
                              d="m15.167 1l.598 1.118c.404.755.606 1.133.472 1.295c-.133.162-.573.031-1.454-.23A9.8 9.8 0 0 0 12 2.78c-5.247 0-9.5 4.128-9.5 9.22a8.97 8.97 0 0 0 1.27 4.61M8.834 23l-.598-1.118c-.404-.756-.606-1.134-.472-1.295c.133-.162.573-.032 1.454.23c.88.261 1.815.402 2.783.402c5.247 0 9.5-4.128 9.5-9.22a8.97 8.97 0 0 0-1.27-4.609"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="relative">
            <div
                wire:loading.flex
                wire:target="openDropdown,reloadCart,increase,decrease,remove"
                class="absolute inset-0 z-10 hidden items-center justify-center bg-white/70 backdrop-blur-[2px] dark:bg-slate-900/70"
            >
                <div
                    class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none">
                        <path d="M20 12a8 8 0 1 1-2.34-5.66" stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round"/>
                    </svg>
                    در حال به‌روزرسانی...
                </div>
            </div>

            @if(count($summary['items']) > 0)
                <div class="max-h-[24rem] overflow-y-auto">
                    @foreach($summary['items'] as $item)
                        <div class="border-b border-slate-100 px-4 py-4 last:border-b-0 dark:border-slate-800">
                            <div class="flex gap-3">
                                <div
                                    class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800">
                                    @if($item['image_url'])
                                        <img
                                            src="{{ $item['image_url'] }}"
                                            alt="{{ $item['title'] }}"
                                            class="h-full w-full object-cover"
                                        >
                                    @else
                                        <div
                                            class="grid h-full w-full place-items-center text-slate-400 dark:text-slate-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24"
                                                 fill="none">
                                                <path d="M8 8.5V7a4 4 0 0 1 8 0v1.5" stroke="currentColor"
                                                      stroke-width="1.8" stroke-linecap="round"/>
                                                <path
                                                    d="M5.7 8.2c.12-1.1 1.05-1.95 2.16-1.95h8.28c1.11 0 2.04.85 2.16 1.95l.86 8.1A3.35 3.35 0 0 1 15.83 20H8.17a3.35 3.35 0 0 1-3.33-3.7l.86-8.1Z"
                                                    stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="line-clamp-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ $item['title'] }}
                                    </div>

                                    @if($item['subtitle'])
                                        <div class="mt-1 line-clamp-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
                                            {{ $item['subtitle'] }}
                                        </div>
                                    @endif

                                    @if(count($item['children']) > 0)
                                        <div
                                            class="mt-2 space-y-1 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/70">
                                            @foreach($item['children'] as $child)
                                                <div class="flex items-center justify-between gap-2 text-[0.7rem]">
                                                    <span class="line-clamp-1 text-slate-500 dark:text-slate-400">
                                                        + {{ $child['title'] }}
                                                    </span>

                                                    <span class="shrink-0 font-bold text-slate-600 dark:text-slate-300">
                                                        {{ number_format($child['total_amount']) }} تومان
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div
                                            class="inline-flex items-center overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
                                            <button
                                                type="button"
                                                x-on:click="$dispatch('product-buy-box-sync-started')"
                                                wire:click="decrease({{ $item['id'] }})"
                                                class="cursor-pointer grid h-8 w-8 place-items-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                                aria-label="کم کردن تعداد"
                                            >
                                                −
                                            </button>

                                            <span
                                                class="grid h-8 min-w-8 place-items-center px-2 text-xs font-extrabold text-slate-800 dark:text-slate-100">
                                                {{ $item['quantity'] }}
                                            </span>

                                            <button
                                                type="button"
                                                wire:click="increase({{ $item['id'] }})"
                                                x-on:click="$dispatch('product-buy-box-sync-started')"
                                                @disabled(! $item['can_increase'])
                                                title="{{ ! $item['can_increase'] ? 'حداکثر تعداد مجاز برای این آیتم انتخاب شده است.' : '' }}"
                                                class="cursor-pointer grid h-8 w-8 place-items-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                                aria-label="زیاد کردن تعداد"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <div class="text-left">
                                            <div class="text-sm font-black text-slate-900 dark:text-slate-100">
                                                {{ number_format($item['total_amount']) }}
                                                <span
                                                    class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                            </div>

                                            @if($item['quantity'] > 1)
                                                <div class="mt-0.5 text-[0.65rem] text-slate-400 dark:text-slate-500">
                                                    هر عدد {{ number_format($item['unit_price']) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="$dispatch('product-buy-box-sync-started')"
                                    wire:click="remove({{ $item['id'] }})"
                                    class="cursor-pointer h-9 w-9 [&_svg]:mx-auto [&_svg]:h-4 [&_svg]:w-4 shrink-0 rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 transition hover:bg-rose-100 hover:text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20"
                                    aria-label="حذف از سبد خرید"
                                >
                                    @include('icons.delete-02')
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div
                    class="border-t border-slate-100 bg-slate-50/80 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>جمع جزء</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">
                                {{ number_format($summary['subtotal_amount']) }} تومان
                            </span>
                        </div>

                        @if($summary['discount_amount'] > 0)
                            <div
                                class="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400">
                                <span>تخفیف</span>
                                <span class="font-bold">
                                    {{ number_format($summary['discount_amount']) }} تومان
                                </span>
                            </div>
                        @endif

                        <div
                            class="flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-800">
                            <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
                                مبلغ قابل پرداخت
                            </span>

                            <span class="text-base font-black text-slate-950 dark:text-white">
                                {{ number_format($summary['total_amount']) }}
                                <span class="text-[0.7rem] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a
                            href="{{ $this->cartUrl }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        >
                            مشاهده سبد
                        </a>

                        <a
                            href="{{ $this->checkoutUrl }}"
                            class="inline-flex items-center justify-center rounded-2xl bg-brand-500 px-3 py-2.5 text-xs font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
                        >
                            ادامه پرداخت
                        </a>
                    </div>
                </div>
            @else
                <div class="px-4 py-10 text-center">
                    <div
                        class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                            <path d="M8 8.5V7a4 4 0 0 1 8 0v1.5" stroke="currentColor" stroke-width="1.8"
                                  stroke-linecap="round"/>
                            <path
                                d="M5.7 8.2c.12-1.1 1.05-1.95 2.16-1.95h8.28c1.11 0 2.04.85 2.16 1.95l.86 8.1A3.35 3.35 0 0 1 15.83 20H8.17a3.35 3.35 0 0 1-3.33-3.7l.86-8.1Z"
                                stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                    </div>

                    <div class="mt-4 text-sm font-black text-slate-800 dark:text-slate-100">
                        سبد خریدت خالیه
                    </div>

                    <p class="mx-auto mt-1 max-w-[17rem] text-xs leading-6 text-slate-500 dark:text-slate-400">
                        محصولات و فایل‌هایی که انتخاب می‌کنی اینجا نمایش داده می‌شوند.
                    </p>

                    <a
                        href="{{ Route::has('products.list') ? route('products.list') : '#' }}"
                        class="mt-5 inline-flex items-center justify-center rounded-2xl bg-brand-500 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
                    >
                        مشاهده محصولات
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
