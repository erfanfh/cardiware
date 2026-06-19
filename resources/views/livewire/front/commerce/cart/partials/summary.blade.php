<aside class="min-w-0 lg:col-span-4">
    <div class="lg:sticky lg:top-28">
        <x-front.cart>
            <div
                class="transition"
                wire:loading.class="opacity-70"
                wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-500">مبلغ قابل پرداخت</div>

                        <div
                            class="mt-2 text-xl font-black text-slate-900 transition"
                            wire:loading.class="animate-pulse"
                            wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon"
                        >
                            {{ $this->money($this->cartTotal) }}
                        </div>
                    </div>

                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M6 6h15l-2 8H8L6 6ZM6 6 5 3H3M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 space-y-2.5 border-t border-slate-100 pt-4">
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500">تعداد محصولات</span>
                        <span class="font-extrabold text-slate-900">{{ $this->itemsCount }} محصول</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500">جمع محصولات</span>
                        <span class="font-extrabold text-slate-900">{{ $this->money($this->cartSubtotal) }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500">تخفیف</span>
                        <span class="font-extrabold text-emerald-700">{{ $this->money($this->cartDiscount) }}</span>
                    </div>

                    <div class="my-3 border-t border-slate-100"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-extrabold text-slate-700">مبلغ نهایی</span>

                        <span
                            class="text-base font-black text-slate-900 transition"
                            wire:loading.class="animate-pulse"
                            wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon"
                        >
                            {{ $this->money($this->cartTotal) }}
                        </span>
                    </div>
                </div>

                <button
                    type="button"
                    @auth
                        @click="checkoutModalOpen = true"
                    @else
                        @click="$dispatch('open-modal', 'auth-modal')"
                    @endauth
                    @disabled($this->mainCartItems->isEmpty() || $cartNeedsRefresh)
                    class="mt-5 inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    @auth
                        ادامه فرآیند پرداخت
                    @else
                        ورود و ادامه پرداخت
                    @endauth

                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M3 10h18M7 15h1m4 0h5M6 19h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>

                <a
                    href="{{ route('products.list') }}"
                    class="mt-3 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50"
                >
                    بازگشت به فروشگاه

                    <span class="[&_svg]:h-3.5 [&_svg]:w-3.5">
                        @include('icons.arrow-left-02')
                    </span>
                </a>
            </div>
        </x-front.cart>
    </div>
</aside>
