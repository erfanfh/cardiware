<aside class="min-w-0 lg:col-span-4">
    <div class="lg:sticky lg:top-28">
        <x-front.cart>
            <div
                class="transition dark:text-slate-100"
                wire:loading.class="opacity-70"
                wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon, removeCoupon"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">مبلغ قابل پرداخت</div>

                        <div
                            class="mt-2 text-xl font-black text-slate-900 dark:text-slate-100 transition"
                            wire:loading.class="animate-pulse"
                            wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon, removeCoupon"
                        >
                            {{ $this->money($this->cartTotal) }}
                        </div>
                    </div>

                    <div
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100
                        dark:bg-brand-900/30 dark:text-brand-400 dark:ring-brand-900/40">
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

                <div class="mt-4 space-y-2.5 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">تعداد محصولات</span>
                        <span
                            class="font-extrabold text-slate-900 dark:text-slate-100">{{ $this->itemsCount }} محصول</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">جمع محصولات</span>
                        <span
                            class="font-extrabold text-slate-900 dark:text-slate-100">{{ $this->money($this->cartSubtotal) }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">
                            تخفیف
                            @if($this->vipDiscountLabel)
                                <span class="mr-1 rounded-lg bg-amber-50 px-1.5 py-0.5 text-[10px] font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">👑 {{ $this->vipDiscountLabel }}</span>
                            @endif
                        </span>
                        <span
                            class="font-extrabold text-emerald-700 dark:text-emerald-400">{{ $this->money($this->cartDiscount) }}</span>
                    </div>

                    @if((int) ($this->cart?->tax_rate_basis_points ?? 0) > 0)
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-slate-500 dark:text-slate-400">
                                مالیات ({{ $this->cartTaxRateLabel() }}٪)
                            </span>
                            <span class="font-extrabold text-amber-700 dark:text-amber-400">
                                {{ $this->money($this->cartTax) }}
                            </span>
                        </div>
                    @endif

                    <div class="my-3 border-t border-slate-100 dark:border-slate-800"></div>

                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-extrabold text-slate-700 dark:text-slate-200">مبلغ نهایی</span>

                        <span
                            class="text-base font-black text-slate-900 dark:text-slate-100 transition"
                            wire:loading.class="animate-pulse"
                            wire:target="increaseQuantity, decreaseQuantity, removeItem, applyCoupon, removeCoupon"
                        >
                            {{ $this->money($this->cartTotal) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <form wire:submit.prevent="applyCoupon" class="space-y-3">
                        <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-200">
                            کدتخفیف
                        </label>

                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model.defer="couponCode"
                                dir="ltr"
                                class="h-11 min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100
                                dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:focus:border-brand-500 dark:focus:ring-brand-900/30"
                                @disabled($this->mainCartItems->isEmpty() || $cartNeedsRefresh)
                            />

                            <button
                                type="submit"
                                class="cursor-pointer inline-flex h-11 shrink-0 items-center justify-center rounded-2xl bg-slate-900 px-4 text-xs font-extrabold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50
                                dark:bg-slate-700 dark:text-slate-50 dark:hover:bg-slate-600"
                                wire:loading.attr="disabled"
                                wire:target="applyCoupon"
                                @disabled($this->mainCartItems->isEmpty() || $cartNeedsRefresh)
                            >
                                <span wire:loading.remove wire:target="applyCoupon">اعمال</span>
                                <span wire:loading wire:target="applyCoupon">...</span>
                            </button>
                        </div>
                    </form>

                    @foreach($this->cart?->appliedCouponSnapshots() ?? [] as $appliedCoupon)
                        <div
                            wire:key="applied-coupon-{{ $appliedCoupon['coupon_id'] }}"
                            class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs
                            dark:border-emerald-900/40 dark:bg-emerald-900/20"
                        >
                            <div class="min-w-0">
                                <div class="font-extrabold text-emerald-700 dark:text-emerald-300">
                                    کدتخفیف اعمال شده
                                </div>

                                <div
                                    class="mt-1 flex flex-wrap items-center gap-2 text-emerald-700 dark:text-emerald-300">
                                    <span dir="ltr" class="font-black">
                                        {{ $appliedCoupon['code'] }}
                                    </span>

                                    @if($appliedCoupon['discount_amount'])
                                        <span class="text-emerald-600 dark:text-emerald-400">
                                            {{ $this->money((int) $appliedCoupon['discount_amount']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <button
                                type="button"
                                wire:click="removeCoupon(@js($appliedCoupon['code']))"
                                wire:loading.attr="disabled"
                                wire:target="removeCoupon"
                                class="cursor-pointer shrink-0 text-xs font-extrabold text-rose-600 transition hover:text-rose-700 disabled:opacity-50"
                            >
                                حذف
                            </button>
                        </div>
                    @endforeach

                    @if($checkoutError)
                        <div
                            class="mt-3 rounded-2xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs leading-6 text-rose-700 dark:border-rose-900/40 dark:bg-rose-900/20 dark:text-rose-300">
                            {{ $checkoutError }}
                        </div>
                    @endif

                    @if($checkoutNotice)
                        <div
                            class="mt-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs leading-6 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                            {{ $checkoutNotice }}
                        </div>
                    @endif
                </div>

                @if($this->checkoutTermsRequired)
                    <label
                        class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs leading-6 text-slate-600 transition hover:border-brand-200 hover:bg-brand-50/60 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-300 dark:hover:border-brand-800 dark:hover:bg-brand-900/10"
                    >
                        <input
                            type="checkbox"
                            x-model="termsAccepted"
                            class="mt-1 h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-brand-500 focus:ring-brand-400 dark:border-slate-600 dark:bg-slate-900"
                        />

                        <span>
                            با
                            @if($this->checkoutTermsUrl)
                                <a
                                    href="{{ $this->checkoutTermsUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="font-extrabold text-brand-700 underline decoration-brand-300 underline-offset-4 hover:text-brand-800 dark:text-brand-400 dark:hover:text-brand-300"
                                    @click.stop
                                >
                                    قوانین و مقررات سایت
                                </a>
                            @else
                                <span class="font-extrabold text-slate-800 dark:text-slate-100">قوانین و مقررات سایت</span>
                            @endif
                            موافقم.
                        </span>
                    </label>
                @endif

                <button
                    type="button"
                    @auth
                        @click="checkoutModalOpen = true"
                    @else
                        @click="$dispatch('open-modal', 'auth-modal')"
                    @endauth
                    :disabled="@js($this->mainCartItems->isEmpty() || $cartNeedsRefresh) || (@js(auth()->check() && $this->checkoutTermsRequired) && !termsAccepted)"
                    class="mt-5 inline-flex h-12 w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 text-sm font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50
                    dark:shadow-none dark:hover:bg-brand-600"
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
                    class="mt-3 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50
                    dark:border-slate-700 dark:bg-slate-900 dark:text-brand-400 dark:hover:bg-slate-800 dark:hover:border-slate-600"
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
