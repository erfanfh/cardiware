<div
    x-data="{
        checkoutModalOpen: @js($openCheckoutOnLoad),

        paymentMethod: @js($paymentMethod),

        checkoutSubmitting: false,

        async submitCheckout() {
            if (this.checkoutSubmitting) {
                return;
            }

            /*
             * روش انتخاب شده در همین لحظه snapshot می‌شود
             * و همراه همان درخواست checkout ارسال می‌شود.
             */
            const selectedPaymentMethod = this.paymentMethod;

            this.checkoutSubmitting = true;

            try {
                await this.$wire.checkout(
                    selectedPaymentMethod
                );
            } finally {
                this.checkoutSubmitting = false;
            }
        }
    }"
>
    <main class="mx-auto max-w-7xl px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8">
        <section class="grid grid-cols-1 gap-4 lg:grid-cols-12 lg:gap-6">
            <div class="min-w-0 lg:col-span-8">
                <div class="space-y-3 sm:space-y-4">
                    @forelse($this->mainCartItems as $cartItem)
                        @include('livewire.front.commerce.cart.partials.cart-item', [
                            'cartItem' => $cartItem,
                            'addonItems' => $this->addonItemsByParentId->get($cartItem->id, collect()),
                        ])
                    @empty
                        <div
                            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 text-center shadow-sm sm:rounded-3xl sm:p-8"
                        >
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-2xl"
                            >
                                🛒
                            </div>

                            <div class="mt-4 text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                سبد خرید شما خالی است.
                            </div>

                            <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                هنوز محصولی به سبد خرید اضافه نکرده‌اید.
                            </p>

                            <a
                                href="{{ route('products.list') }}"
                                class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 dark:bg-slate-100 px-5 text-xs font-extrabold text-white dark:text-slate-900 transition hover:bg-slate-800 dark:hover:bg-slate-200 sm:w-auto"
                            >
                                مشاهده محصولات
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            @include('livewire.front.commerce.cart.partials.summary')
        </section>
    </main>

    @auth
        @include('livewire.front.commerce.cart.partials.checkout-modal')
    @endauth

    @guest
        <livewire:front.identity.auth-modal
            name="auth-modal"
            :claim-current-cart="true"
            :open-checkout-after-auth="true"
        />
    @endguest
</div>
