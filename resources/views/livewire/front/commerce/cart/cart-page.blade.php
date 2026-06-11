<div x-data="{ checkoutModalOpen: @js($openCheckoutOnLoad) }">
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <div class="space-y-4">
                    @forelse($this->mainCartItems as $cartItem)
                        @include('livewire.front.commerce.cart.partials.cart-item', [
                            'cartItem' => $cartItem,
                            'addonItems' => $this->addonItemsByParentId->get($cartItem->id, collect()),
                        ])
                    @empty
                        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl">
                                🛒
                            </div>

                            <div class="mt-4 text-sm font-extrabold text-slate-900">
                                سبد خرید شما خالی است.
                            </div>

                            <p class="mt-2 text-xs leading-6 text-slate-500">
                                هنوز محصولی به سبد خرید اضافه نکرده‌اید.
                            </p>

                            <a href="{{ route('products.list') }}"
                               class="mt-5 inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-xs font-extrabold text-white transition hover:bg-slate-800">
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
