<template x-teleport="body">
    <div
        x-show="shouldShowStickyBuyBar"
        x-cloak
        x-transition:enter="transition ease-out duration-300 motion-reduce:transition-none"
        x-transition:enter-start="translate-y-full opacity-0 sm:translate-y-4 sm:scale-[0.98]"
        x-transition:enter-end="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave="transition ease-in duration-200 motion-reduce:transition-none"
        x-transition:leave-start="translate-y-0 opacity-100 sm:scale-100"
        x-transition:leave-end="translate-y-full opacity-0 sm:translate-y-4 sm:scale-[0.98]"
        :data-product-sticky-buy-bar="productId"
        class="fixed inset-x-0 bottom-0 z-[70] border-t border-slate-200/90 bg-white/95 shadow-[0_-12px_35px_rgba(15,23,42,0.12)] backdrop-blur-xl dark:border-slate-700/90 dark:bg-slate-950/95 dark:shadow-[0_-16px_42px_rgba(0,0,0,0.42)] sm:inset-x-4 sm:bottom-4 sm:mx-auto sm:max-w-3xl sm:rounded-3xl sm:border sm:shadow-[0_18px_60px_rgba(15,23,42,0.18)] sm:dark:shadow-[0_18px_60px_rgba(0,0,0,0.48)]"
        style="padding-bottom: env(safe-area-inset-bottom);"
        role="region"
        aria-label="Quick product purchase"
    >
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-3 py-3 sm:gap-4 sm:px-4 lg:px-5">
            <button
                type="button"
                class="inline-flex h-11 min-w-0 flex-1 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-brand-600 active:bg-brand-700 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/20 dark:bg-brand-600 dark:hover:bg-brand-500 sm:h-12 sm:flex-none sm:min-w-52 sm:px-6"
                aria-controls="product-main-buy-box"
                @click="scrollToMainBuyBox()"
            >
                <span class="shrink-0 [&_svg]:h-4.5 [&_svg]:w-4.5">
                    @include('icons.shopping-cart-02')
                </span>

                <span class="min-w-0 truncate" x-text="stickyBuyActionLabel"></span>
            </button>

            <div class="min-w-0 shrink-0 text-end sm:mr-auto">
                <div class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 sm:text-[11px]" x-text="stickyPriceCaption"></div>

                <div
                    class="mt-0.5 whitespace-nowrap text-sm font-black text-slate-900 dark:text-white sm:text-base"
                    x-text="formatPrice(totalPrice)"
                ></div>

                <div
                    x-show="shouldShowSaleItemSelector && selectedSaleItem?.title"
                    x-cloak
                    class="mt-0.5 hidden max-w-56 truncate text-[10px] font-bold text-slate-400 dark:text-slate-500 sm:block"
                    x-text="selectedSaleItem?.title"
                ></div>
            </div>
        </div>
    </div>
</template>
