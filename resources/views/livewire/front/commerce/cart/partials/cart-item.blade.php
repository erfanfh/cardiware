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
            class="flex flex-col gap-4 p-3 transition sm:p-4 md:flex-row md:items-center md:justify-between
            dark:border-slate-700 dark:bg-slate-900"
            wire:loading.class="opacity-70"
            wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }}), removeItem({{ $cartItem->id }})"
        >
            <div class="flex min-w-0 gap-3 sm:gap-4">
                <div
                    class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 sm:h-24 sm:w-24
                    dark:border-slate-700 dark:bg-slate-800"
                >
                    <img
                        src="{{ $this->itemImage($cartItem) }}"
                        alt="{{ $this->itemTitle($cartItem) }}"
                        class="h-full w-full object-cover"
                    >
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="line-clamp-2 text-sm font-black leading-7 text-slate-900 sm:line-clamp-1
                        dark:text-slate-100">
                        <a href="{{ $this->itemLink($cartItem) }}">
                            {{ $this->itemTitle($cartItem) }}
                        </a>
                    </h3>

                    <p class="mt-1 line-clamp-2 text-xs leading-6 text-slate-500 sm:line-clamp-1
                        dark:text-slate-400">
                        {{ $this->itemDescription($cartItem) }}
                    </p>

                    <div class="mt-3 flex flex-wrap items-center gap-2 text-[11px] font-bold leading-5 text-slate-500
                        dark:text-slate-400">
                        <span>قیمت واحد: {{ $this->money($unitPrice) }}</span>

                        @if($quantity > 1)
                            <span class="text-slate-300 dark:text-slate-600">•</span>
                            <span>تعداد: {{ $quantity }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col gap-3 border-t border-slate-100 pt-3 md:items-end md:border-t-0 md:pt-0
                dark:border-slate-800">
                <div class="flex items-center justify-between gap-3 md:block md:text-left">
                    <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                        مبلغ این آیتم
                    </div>

                    <div
                        class="text-base font-black text-slate-900 transition md:mt-1 dark:text-slate-100"
                        wire:loading.class="animate-pulse"
                        wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                    >
                        {{ $this->money($totalPrice) }}
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 md:justify-end">
                    @if($canChangeQuantity)
                        <div
                            class="flex items-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 transition
                            dark:border-slate-700 dark:bg-slate-800"
                            wire:loading.class="opacity-70"
                            wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                        >
                            <button
                                type="button"
                                wire:click="decreaseQuantity({{ $cartItem->id }})"
                                wire:loading.attr="disabled"
                                wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                @disabled($quantity <= 1)
                                class="flex h-10 w-10 cursor-pointer items-center justify-center text-lg font-black text-slate-600 transition hover:bg-white
                                dark:text-slate-300 dark:hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                −
                            </button>

                            <div
                                class="flex h-10 min-w-10 items-center justify-center border-x border-slate-200 bg-white px-3 text-sm font-black text-slate-900
                                dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
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

                            <button
                                type="button"
                                wire:click="increaseQuantity({{ $cartItem->id }})"
                                wire:loading.attr="disabled"
                                wire:target="increaseQuantity({{ $cartItem->id }}), decreaseQuantity({{ $cartItem->id }})"
                                @disabled($quantity >= $maxQuantity)
                                class="flex h-10 w-10 cursor-pointer items-center justify-center text-lg font-black text-slate-600 transition hover:bg-white
                                dark:text-slate-300 dark:hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                +
                            </button>
                        </div>
                    @endif

                    <button
                        type="button"
                        x-on:click="$dispatch('open-modal', '{{ $removeMainModalName }}')"
                        wire:loading.attr="disabled"
                        wire:target="removeItem({{ $cartItem->id }})"
                        class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100
                        dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-400 disabled:cursor-not-allowed disabled:opacity-50"
                    >
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
            <div class="border-t border-slate-100 bg-slate-50/70 px-3 py-3 sm:px-4
                dark:border-slate-800 dark:bg-slate-900/50">
                <div class="mb-2 text-[11px] font-extrabold text-slate-500 dark:text-slate-400">
                    خدمات افزوده انتخاب‌شده
                </div>

                <div class="space-y-2">
                    @foreach($addonItems as $addonItem)
                        @php
                            $removeAddonModalName = 'remove-cart-item-' . $addonItem->id;
                        @endphp

                        <div wire:key="addon-cart-item-wrapper-{{ $addonItem->id }}">
                            <div
                                class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 transition sm:flex-row sm:items-center sm:justify-between
                                dark:border-slate-700 dark:bg-slate-900"
                                wire:loading.class="opacity-70"
                                wire:target="removeItem({{ $addonItem->id }})"
                            >
                                <div class="min-w-0">
                                    <div class="line-clamp-1 text-xs font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ $this->itemTitle($addonItem) }}
                                    </div>

                                    <div class="mt-1 text-[11px] leading-5 text-slate-500 dark:text-slate-400">
                                        {{ $this->itemBadge($addonItem) }}
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center justify-between gap-2 sm:justify-end">
                                    <div
                                        class="text-xs font-black text-slate-900 transition dark:text-slate-100"
                                        wire:loading.class="animate-pulse"
                                        wire:target="removeItem({{ $addonItem->id }})"
                                    >
                                        {{ $this->money($this->itemTotal($addonItem)) }}
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="$dispatch('open-modal', '{{ $removeAddonModalName }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="removeItem({{ $addonItem->id }})"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 transition hover:bg-rose-100
                                        dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-400 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
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
                                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3
                                        dark:border-rose-900/40 dark:bg-rose-950/30">
                                        <div class="text-sm font-black text-rose-700 dark:text-rose-300">
                                            {{ $this->itemTitle($addonItem) }}
                                        </div>

                                        <p class="mt-1 text-xs leading-6 text-rose-600 dark:text-rose-400">
                                            این مورد از سبد خرید شما حذف می‌شود.
                                        </p>
                                    </div>

                                    <x-slot name="footer">
                                        <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
                                            <button
                                                type="button"
                                                x-on:click="$dispatch('close-modal', '{{ $removeAddonModalName }}')"
                                                class="h-10 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50
                                                dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                            >
                                                انصراف
                                            </button>

                                            <button
                                                type="button"
                                                x-on:click="$dispatch('close-modal', '{{ $removeAddonModalName }}'); $wire.removeItem({{ $addonItem->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="removeItem({{ $addonItem->id }})"
                                                class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 text-xs font-extrabold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                <span wire:loading.remove
                                                      wire:target="removeItem({{ $addonItem->id }})">
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
            <div class="flex items-start gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3
                dark:border-rose-900/40 dark:bg-rose-950/30">
                <div
                    class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-rose-100 bg-white dark:bg-slate-900">
                    <img
                        src="{{ $this->itemImage($cartItem) }}"
                        alt="{{ $this->itemTitle($cartItem) }}"
                        class="h-full w-full object-cover"
                    >
                </div>

                <div class="min-w-0">
                    <div class="line-clamp-1 text-sm font-black text-rose-700 dark:text-rose-300">
                        {{ $this->itemTitle($cartItem) }}
                    </div>

                    <p class="mt-1 text-xs leading-6 text-rose-600 dark:text-rose-400">
                        با حذف این محصول، خدمات افزوده مرتبط با آن هم از سبد خرید حذف می‌شود.
                    </p>
                </div>
            </div>

            <x-slot name="footer">
                <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
                    <button
                        type="button"
                        x-on:click="$dispatch('close-modal', '{{ $removeMainModalName }}')"
                        class="h-10 cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600 transition hover:bg-slate-50
                        dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        انصراف
                    </button>

                    <button
                        type="button"
                        x-on:click="$dispatch('close-modal', '{{ $removeMainModalName }}'); $wire.removeItem({{ $cartItem->id }})"
                        wire:loading.attr="disabled"
                        wire:target="removeItem({{ $cartItem->id }})"
                        class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-2xl bg-rose-600 px-4 text-xs font-extrabold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="removeItem({{ $cartItem->id }})">
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
