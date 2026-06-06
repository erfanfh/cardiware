<aside
    class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:col-span-4 lg:border-r lg:border-t-0 lg:p-5"
>
    <div class="sticky top-5 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xs font-bold text-slate-500">مبلغ قابل پرداخت</div>
                <div class="mt-2 text-xl font-black text-slate-900">
                    {{ $this->money($this->cartTotal) }}
                </div>
            </div>

            <div
                class="grid h-10 w-10 place-items-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M6 6h15l-2 8H8L6 6ZM6 6 5 3H3M9 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM18 20a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"/>
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

            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="text-slate-500">مالیات</span>
                <span class="font-extrabold text-slate-900">{{ $this->money($this->cartTax) }}</span>
            </div>

            <div class="my-3 border-t border-slate-100"></div>

            <div class="flex items-center justify-between gap-3">
                <span class="text-sm font-extrabold text-slate-700">مبلغ نهایی</span>
                <span class="text-base font-black text-slate-900">{{ $this->money($this->cartTotal) }}</span>
            </div>
        </div>

        <form
            wire:submit.prevent="applyCoupon"
            class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4"
        >
            <h3 class="text-sm font-extrabold text-slate-900">کد تخفیف دارید؟</h3>
            <p class="mt-1 text-xs leading-6 text-slate-500">
                کد تخفیف خود را وارد کنید.
            </p>

            <div class="mt-3 flex gap-2">
                <input
                    type="text"
                    wire:model="couponCode"
                    placeholder="مثلا MW2026"
                    class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-bold outline-none transition placeholder:text-slate-400 focus:border-brand-300 focus:ring-4 focus:ring-brand-100"
                >

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="applyCoupon"
                    class="shrink-0 cursor-pointer rounded-2xl bg-slate-900 px-4 py-3 text-sm font-extrabold text-white transition hover:opacity-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="applyCoupon">اعمال</span>
                    <span wire:loading wire:target="applyCoupon">...</span>
                </button>
            </div>

            @error('couponCode')
            <p class="mt-2 text-xs font-bold text-red-600">
                {{ $message }}
            </p>
            @enderror
        </form>

        <button
            type="button"
            @click="checkoutModalOpen = true"
            @disabled($this->mainCartItems->isEmpty())
            class="mt-5 inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-md shadow-brand-500/20 transition hover:bg-brand-600 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
            ادامه فرآیند پرداخت
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <path
                    d="M3 10h18M7 15h1m4 0h5M6 19h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3Z"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>

        <a href="/"
           class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50">
            بازگشت به فروشگاه
            <span>←</span>
        </a>
    </div>
</aside>
