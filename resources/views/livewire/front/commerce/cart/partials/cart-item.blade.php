@php
    $quantity = (int) ($cartItem->quantity ?? 1);
    $unitPrice = (int) ($cartItem->unit_price ?? 0);
    $totalPrice = $this->itemTotal($cartItem);

    $maxQuantity = $this->itemMaxQuantity($cartItem);
    $canChangeQuantity = $maxQuantity > 1;

    $removeMainModalName = 'remove-cart-item-' . $cartItem->id;
@endphp

<div wire:key="cart-item-wrapper-{{ $cartItem->id }}">
    <x-front.cart class="!p-0 overflow-hidden">
        <div
            class="flex flex-col gap-4 p-4 transition sm:flex-row sm:items-center sm:justify-between"
            wire:loading.class="opacity-70"
            wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }}), removeItem({{ $cartItem->id }})"
        >
            <div class="flex min-w-0 items-start gap-4">
                <div class="h-24 w-24 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                    <img src="{{ $this->itemImage($cartItem) }}"
                         alt="{{ $this->itemTitle($cartItem) }}"
                         class="h-full w-full object-cover">
                </div>

                <div class="min-w-0 pt-1">
                    <h3 class="mt-2 line-clamp-1 text-sm font-black text-slate-900">
                        <a href="{{ $this->itemLink($cartItem) }}">{{ $this->itemTitle($cartItem) }}</a>
                    </h3>

                    <p class="mt-1 line-clamp-1 text-xs leading-6 text-slate-500">
                        {{ $this->itemDescription($cartItem) }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-bold text-slate-500">
                        <span>قیمت واحد: {{ $this->money($unitPrice) }}</span>

                        @if($quantity > 1)
                            <span class="text-slate-300">•</span>
                            <span>تعداد: {{ $quantity }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col gap-3 sm:items-end">
                <div class="text-left">
                    <div class="text-[11px] font-bold text-slate-400">
                        مبلغ این آیتم
                    </div>

                    <div
                        class="mt-1 text-base font-black text-slate-900 transition"
                        wire:loading.class="animate-pulse"
                        wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                    >
                        {{ $this->money($totalPrice) }}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($canChangeQuantity)
                        <div
                            class="flex items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 transition"
                            wire:loading.class="opacity-70"
                            wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                        >
                            <button type="button"
                                    wire:click="decreaseQuantity({{ $cartItem->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                    @disabled($quantity <= 1)
                                    class="cursor-pointer flex h-10 w-10 items-center justify-center text-lg font-black text-slate-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40">
                                −
                            </button>

                            <div
                                class="flex h-10 min-w-10 items-center justify-center border-x border-slate-200 bg-white px-3 text-sm font-black text-slate-900">
                                <span
                                    wire:loading.remove
                                    wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                >
                                    {{ $quantity }}
                                </span>

                                <span
                                    wire:loading
                                    wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                    class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-slate-700"
                                ></span>
                            </div>

                            <button type="button"
                                    wire:click="increaseQuantity({{ $cartItem->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                    @disabled($quantity >= $maxQuantity)
                                    class="cursor-pointer flex h-10 w-10 items-center justify-center text-lg font-black text-slate-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-40">
                                +
                            </button>
                        </div>
                    @endif

                    <button type="button"
                            x-on:click="$dispatch('open-modal', '{{ $removeMainModalName }}')"
                            wire:loading.attr="disabled"
                            wire:target="removeItem({{ $cartItem->id }})"
                            class="cursor-pointer flex h-10 w-10 items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50">
                        <span
                            class="h-4 w-4 [&_svg]:h-4 [&_svg]:w-4"
                            wire:loading.remove
                            wire:target="removeItem({{ $cartItem->id }})"
                        >
                            @include('icons.delete-02')
                        </span>

                        <span
                            wire:loading
                            wire:target="removeItem({{ $cartItem->id }})"
                            class="h-4 w-4 animate-spin rounded-full border-2 border-rose-200 border-t-rose-600"
                        ></span>
                    </button>
                </div>
            </div>
        </div>

        @if($addonItems->isNotEmpty())
            <div class="border-t border-slate-100 bg-slate-50/70 px-4 py-3">
                <div class="mb-2 text-[11px] font-extrabold text-slate-500">
                    خدمات افزوده انتخاب‌شده
                </div>

                <div class="space-y-2">
                    @foreach($addonItems as $addonItem)
                        @php
                            $removeAddonModalName = 'remove-cart-item-' . $addonItem->id;
                        @endphp

                        <div wire:key="addon-cart-item-wrapper-{{ $addonItem->id }}">
                            <div
                                class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 transition"
                                wire:loading.class="opacity-70"
                                wire:target="removeItem({{ $addonItem->id }})"
                            >
                                <div class="min-w-0">
                                    <div class="line-clamp-1 text-xs font-extrabold text-slate-800">
                                        {{ $this->itemTitle($addonItem) }}
                                    </div>

                                    <div class="mt-1 text-[11px] text-slate-500">
                                        {{ $this->itemBadge($addonItem) }}
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <div
                                        class="text-xs font-black text-slate-900 transition"
                                        wire:loading.class="animate-pulse"
                                        wire:target="removeItem({{ $addonItem->id }})"
                                    >
                                        {{ $this->money($this->itemTotal($addonItem)) }}
                                    </div>

                                    <button type="button"
                                            x-on:click="$dispatch('open-modal', '{{ $removeAddonModalName }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="removeItem({{ $addonItem->id }})"
                                            class="cursor-pointer flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-50">
                                        <span
                                            wire:loading.remove
                                            wire:target="removeItem({{ $addonItem->id }})"
                                            class="h-3.5 w-3.5 [&_svg]:h-3.5 [&_svg]:w-3.5"
                                        >
                                            @include('icons.delete-02')
                                        </span>

                                        <span
                                            wire:loading
                                            wire:target="removeItem({{ $addonItem->id }})"
                                            class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-rose-200 border-t-rose-600"
                                        ></span>
                                    </button>
                                </div>
                            </div>

                            <div wire:key="remove-addon-modal-wrapper-{{ $addonItem->id }}">
                                <x-front.modal
                                    :name="$removeAddonModalName"
                                    title="حذف خدمت افزوده"
                                    description="آیا مطمئن هستید که می‌خواهید این خدمت افزوده را از سبد خرید حذف کنید؟"
                                    maxWidth="max-w-md"
                                >
                                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                                        <div class="text-sm font-black text-rose-700">
                                            {{ $this->itemTitle($addonItem) }}
                                        </div>

                                        <p class="mt-1 text-xs leading-6 text-rose-600">
                                            این مورد از سبد خرید شما حذف می‌شود.
                                        </p>
                                    </div>

                                    <x-slot name="footer">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button"
                                                    x-on:click="$dispatch('close-modal', '{{ $removeAddonModalName }}')"
                                                    class="cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50">
                                                انصراف
                                            </button>

                                            <button type="button"
                                                    x-on:click="$dispatch('close-modal', '{{ $removeAddonModalName }}'); $wire.removeItem({{ $addonItem->id }})"
                                                    wire:loading.attr="disabled"
                                                    wire:target="removeItem({{ $addonItem->id }})"
                                                    class="cursor-pointer inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">
                                                <span
                                                    wire:loading.remove
                                                    wire:target="removeItem({{ $addonItem->id }})"
                                                >
                                                    حذف شود
                                                </span>

                                                <span
                                                    wire:loading
                                                    wire:target="removeItem({{ $addonItem->id }})"
                                                    class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                                                ></span>
                                            </button>
                                        </div>
                                    </x-slot>
                                </x-front.modal>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-front.cart>

    <div wire:key="remove-main-modal-wrapper-{{ $cartItem->id }}">
        <x-front.modal
            :name="$removeMainModalName"
            title="حذف محصول از سبد خرید"
            description="آیا مطمئن هستید که می‌خواهید این محصول را از سبد خرید حذف کنید؟"
            maxWidth="max-w-md"
        >
            <div class="flex items-start gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3">
                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-rose-100 bg-white">
                    <img src="{{ $this->itemImage($cartItem) }}"
                         alt="{{ $this->itemTitle($cartItem) }}"
                         class="h-full w-full object-cover">
                </div>

                <div class="min-w-0">
                    <div class="line-clamp-1 text-sm font-black text-rose-700">
                        {{ $this->itemTitle($cartItem) }}
                    </div>

                    <p class="mt-1 text-xs leading-6 text-rose-600">
                        با حذف این محصول، خدمات افزوده مرتبط با آن هم از سبد خرید حذف می‌شود.
                    </p>
                </div>
            </div>

            <x-slot name="footer">
                <div class="flex items-center justify-end gap-2">
                    <button type="button"
                            x-on:click="$dispatch('close-modal', '{{ $removeMainModalName }}')"
                            class="cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50">
                        انصراف
                    </button>

                    <button type="button"
                            x-on:click="$dispatch('close-modal', '{{ $removeMainModalName }}'); $wire.removeItem({{ $cartItem->id }})"
                            wire:loading.attr="disabled"
                            wire:target="removeItem({{ $cartItem->id }})"
                            class="cursor-pointer inline-flex items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60">
                        <span
                            wire:loading.remove
                            wire:target="removeItem({{ $cartItem->id }})"
                        >
                            حذف شود
                        </span>

                        <span
                            wire:loading
                            wire:target="removeItem({{ $cartItem->id }})"
                            class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"
                        ></span>
                    </button>
                </div>
            </x-slot>
        </x-front.modal>
    </div>
</div>
