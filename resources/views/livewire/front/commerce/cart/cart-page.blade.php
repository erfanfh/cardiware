<div x-data="{ checkoutModalOpen: false }">
    <main class="mx-auto max-w-7xl px-4 py-8">
        <section>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900">آیتم‌های سبد خرید</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            {{ $this->itemsCount }} محصول در سبد خرید قرار دارد.
                        </p>
                    </div>

                    <span
                        class="inline-flex w-fit items-center gap-2 rounded-2xl border border-slate-200 bg-slate-100 px-3 py-2 text-[11px] font-extrabold text-slate-700">
                        مجموع: {{ $this->money($this->cartTotal) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12">
                    <div class="min-w-0 p-5 sm:p-7 lg:col-span-8 lg:p-9">
                        <div class="space-y-3">
                            @forelse($this->mainCartItems as $cartItem)
                                @include('livewire.front.commerce.cart.partials.cart-item', [
                                    'cartItem' => $cartItem,
                                    'addonItems' => $this->addonItemsByParentId->get($cartItem->id, collect()),
                                ])
                            @empty
                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center">
                                    <div class="text-sm font-extrabold text-slate-800">
                                        سبد خرید شما خالی است.
                                    </div>

                                    <p class="mt-2 text-xs leading-6 text-slate-500">
                                        هنوز محصولی به سبد خرید اضافه نکرده‌اید.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @include('livewire.front.commerce.cart.partials.summary')
                </div>
            </div>
        </section>
    </main>

    @include('livewire.front.commerce.cart.partials.checkout-modal')
</div>
