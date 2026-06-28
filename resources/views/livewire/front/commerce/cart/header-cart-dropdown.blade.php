<div
    x-data="{ open: false }"
    class="relative"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
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
        class="relative grid h-10 w-10 cursor-pointer place-items-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800 [&_svg]:h-[1.125rem] [&_svg]:w-[1.125rem]"
        aria-label="سبد خرید"
    >
        @include('icons.shopping-basket-03')

        @if($summary['items_count'] > 0)
            <span
                class="pointer-events-none absolute -left-1 -top-1 h-5 w-5 animate-[notificationPing_1.8s_ease-out_infinite] rounded-full bg-brand-500/35"></span>
            <span
                class="pointer-events-none absolute -left-1 -top-1 h-5 w-5 animate-pulse rounded-full bg-brand-500/30 blur-[6px]"></span>

            <span
                class="absolute -left-1 -top-1 grid h-[1.1rem] min-w-[1.1rem] animate-[badgePop_1.8s_ease-in-out_infinite] place-items-center rounded-full border-2 border-white bg-brand-500 px-1 text-[10px] font-bold leading-none text-white shadow-lg shadow-brand-500/30 dark:border-slate-900">
                {{ $summary['items_count'] > 9 ? '9+' : $summary['items_count'] }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="fixed inset-0 z-[100] flex items-end justify-center sm:absolute sm:inset-auto sm:left-0 sm:top-full sm:mt-3 sm:block"
        style="display: none;"
    >
        <div
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm sm:hidden"
            @click="open = false"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full opacity-100 sm:translate-y-2 sm:scale-95 sm:opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
            x-transition:leave-end="translate-y-full opacity-100 sm:translate-y-2 sm:scale-95 sm:opacity-0"
            class="relative w-full overflow-hidden rounded-t-3xl border border-slate-200 bg-white shadow-2xl sm:w-[26rem] sm:max-w-[calc(100vw-1rem)] sm:rounded-3xl dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30 max-sm:top-[9.5rem] max-sm:rounded-none"
            @click.stop
        >
            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            سبد خرید
                        </div>

                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $summary['items_count'] }} آیتم در سبد خرید شما
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button
                            type="button"
                            wire:click="reloadCart"
                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-white hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-950 dark:hover:text-slate-100"
                            aria-label="به‌روزرسانی سبد خرید"
                        >
                            <svg wire:loading.class="animate-spin" wire:target="reloadCart,toggle"
                                 xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24">
                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="1.5"
                                      d="m15.167 1l.598 1.118c.404.755.606 1.133.472 1.295c-.133.162-.573.031-1.454-.23A9.8 9.8 0 0 0 12 2.78c-5.247 0-9.5 4.128-9.5 9.22a8.97 8.97 0 0 0 1.27 4.61M8.834 23l-.598-1.118c-.404-.756-.606-1.134-.472-1.295c.133-.162.573-.032 1.454.23c.88.261 1.815.402 2.783.402c5.247 0 9.5-4.128 9.5-9.22a8.97 8.97 0 0 0-1.27-4.609"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="open = false"
                            class="grid h-9 w-9 place-items-center rounded-2xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-slate-800 sm:hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            aria-label="بستن"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
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
                    <div class="max-h-[65vh] overflow-y-auto sm:max-h-[24rem]">
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
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                     viewBox="0 0 24 24" fill="none">
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
                                        <div
                                            class="line-clamp-1 text-sm font-extrabold text-slate-800 dark:text-slate-100">
                                            {{ $item['title'] }}
                                        </div>

                                        @if($item['subtitle'])
                                            <div
                                                class="mt-1 line-clamp-1 text-[0.7rem] text-slate-500 dark:text-slate-400">
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

                                                        <span
                                                            class="shrink-0 font-bold text-slate-600 dark:text-slate-300">
                                                            {{ number_format($child['total_amount']) }} تومان
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div
                                            class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div
                                                class="inline-flex w-fit items-center overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-950">
                                                <button
                                                    type="button"
                                                    x-on:click="$dispatch('product-buy-box-sync-started')"
                                                    wire:click="decrease({{ $item['id'] }})"
                                                    class="grid h-8 w-8 cursor-pointer place-items-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
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
                                                    class="grid h-8 w-8 cursor-pointer place-items-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                                                    aria-label="زیاد کردن تعداد"
                                                >
                                                    +
                                                </button>
                                            </div>

                                            <div class="text-left">
                                                @if($item['has_discount'])
                                                    <div
                                                        class="mb-0.5 whitespace-nowrap text-[0.7rem] font-bold text-slate-400 line-through decoration-brand-400/70 decoration-2 dark:text-slate-500"
                                                    >
                                                        {{ number_format($item['regular_total_amount']) }}
                                                        <span class="text-[0.6rem]">تومان</span>
                                                    </div>

                                                    <div
                                                        class="whitespace-nowrap text-sm font-black text-brand-600 dark:text-brand-400">
                                                        {{ number_format($item['total_amount']) }}
                                                        <span
                                                            class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                                    </div>

                                                    @if($item['quantity'] > 1)
                                                        <div
                                                            class="mt-0.5 text-[0.65rem] text-slate-400 dark:text-slate-500">
                                                            هر عدد
                                                            <span
                                                                class="line-through decoration-brand-400/70 decoration-2">
                                                                {{ number_format($item['regular_unit_price']) }}
                                                            </span>
                                                            <span class="font-bold text-brand-500 dark:text-brand-400">
                                                                {{ number_format($item['unit_price']) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">
                                                        {{ number_format($item['total_amount']) }}
                                                        <span
                                                            class="text-[0.65rem] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                                    </div>

                                                    @if($item['quantity'] > 1)
                                                        <div
                                                            class="mt-0.5 text-[0.65rem] text-slate-400 dark:text-slate-500">
                                                            هر عدد {{ number_format($item['unit_price']) }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="$dispatch('product-buy-box-sync-started')"
                                        wire:click="remove({{ $item['id'] }})"
                                        class="h-9 w-9 shrink-0 cursor-pointer rounded-2xl border border-rose-100 bg-rose-50 text-rose-500 transition hover:bg-rose-100 hover:text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300 dark:hover:bg-rose-500/20 [&_svg]:mx-auto [&_svg]:h-4 [&_svg]:w-4"
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
                                    <span
                                        class="text-[0.7rem] font-bold text-slate-500 dark:text-slate-400">تومان</span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a
                                href="{{ $this->cartUrl }}"
                                class="inline-flex h-11 items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 text-xs font-extrabold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                مشاهده سبد
                            </a>

                            <a
                                href="{{ $this->checkoutUrl }}"
                                class="inline-flex h-11 items-center justify-center rounded-2xl bg-brand-500 px-3 text-xs font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
                            >
                                ادامه پرداخت
                            </a>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-10 text-center">
                        <div
                            class="mx-auto grid h-14 w-14 place-items-center rounded-2xl border border-slate-200 bg-slate-50 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500">
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
                            class="mt-5 inline-flex h-10 items-center justify-center rounded-2xl bg-brand-500 px-4 text-xs font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-400"
                        >
                            مشاهده محصولات
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
