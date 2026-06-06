<article
    wire:key="cart-item-{{ $cartItem->id }}"
    class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md sm:flex-row sm:items-center"
>
    <img
        src="{{ $this->itemImage($cartItem) }}"
        alt="{{ $this->itemTitle($cartItem) }}"
        class="h-36 w-full rounded-2xl object-cover sm:h-24 sm:w-32 sm:shrink-0"
    >

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <h3 class="line-clamp-1 text-sm font-extrabold text-slate-900">
                {{ $this->itemTitle($cartItem) }}
            </h3>

            <span
                class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-extrabold text-emerald-700">
                موجود
            </span>
        </div>

        <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500">
            {{ $this->itemDescription($cartItem) }}
        </p>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span
                class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                {{ $this->itemBadge($cartItem) }}
            </span>

            @if((int) ($cartItem->quantity ?? 1) > 1)
                <span
                    class="rounded-xl border border-slate-200 bg-slate-100 px-2 py-1 text-[11px] font-extrabold text-slate-700">
                    تعداد: {{ $cartItem->quantity }}
                </span>
            @endif
        </div>

        @if($addonItems->isNotEmpty())
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span
                            class="grid h-8 w-8 place-items-center rounded-xl bg-white text-brand-700 ring-1 ring-slate-200">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round"/>
                            </svg>
                        </span>

                        <div>
                            <div class="text-xs font-extrabold text-slate-800">
                                خدمات افزوده این محصول
                            </div>

                            <div class="mt-0.5 text-[11px] text-slate-500">
                                این موارد همراه همین محصول محاسبه می‌شوند.
                            </div>
                        </div>
                    </div>

                    <span
                        class="rounded-xl border border-slate-200 bg-white px-2 py-1 text-[10px] font-extrabold text-slate-500">
                        {{ $addonItems->count() }} مورد
                    </span>
                </div>

                <div class="space-y-2">
                    @foreach($addonItems as $addonItem)
                        @include('livewire.front.commerce.cart.partials.addon-item', [
                            'addonItem' => $addonItem,
                        ])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div
        class="flex shrink-0 items-center justify-between gap-3 border-t border-slate-100 pt-3 sm:block sm:border-t-0 sm:pt-0 sm:text-left">
        <div>
            <div class="text-sm font-black text-slate-900">
                {{ $this->money($this->itemTotal($cartItem)) }}
            </div>

            <button
                type="button"
                wire:click="removeItem({{ $cartItem->id }})"
                wire:loading.attr="disabled"
                wire:target="removeItem({{ $cartItem->id }})"
                class="mt-2 inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-red-100 bg-red-50 text-red-500 shadow-sm transition hover:border-red-200 hover:bg-red-100 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                aria-label="حذف از سبد خرید"
            >
                <svg wire:loading.remove wire:target="removeItem({{ $cartItem->id }})" class="h-4 w-4"
                     viewBox="0 0 24 24" fill="none">
                    <path d="M5 7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M8 7l.6 12.2A2 2 0 0 0 10.6 21h2.8a2 2 0 0 0 2-1.8L16 7"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M9.5 7V5.8A1.8 1.8 0 0 1 11.3 4h1.4a1.8 1.8 0 0 1 1.8 1.8V7"
                          stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>

                <svg wire:loading wire:target="removeItem({{ $cartItem->id }})" class="h-4 w-4 animate-spin"
                     viewBox="0 0 24 24" fill="none">
                    <path d="M12 3a9 9 0 1 1-9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </div>
</article>
