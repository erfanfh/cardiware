<x-front.cart>
    <div
        x-data="productBuyBox({
            saleItems: @js($saleItems),
            addons: @js($addons),
            purchaseState: @js($purchaseState),
            hasAvailableSaleItems: @js($hasAvailableSaleItems),
            cartUrl: @js(route('cart.index')),
        })"
    >
        <template x-if="!hasAvailableSaleItems">
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 8V12"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            />
                            <path
                                d="M12 16H12.01"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            />
                            <path
                                d="M10.29 3.86L1.82 18A2 2 0 0 0 3.53 21H20.47A2 2 0 0 0 22.18 18L13.71 3.86A2 2 0 0 0 10.29 3.86Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <div class="text-sm font-black text-rose-900">
                            ناموجود
                        </div>

                        <div class="mt-1 text-xs leading-6 text-rose-700">
                            در حال حاضر هیچ نسخه فعالی برای خرید این محصول وجود ندارد.
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Version selector: only for variable products --}}
        <template x-if="shouldShowSaleItemSelector">
            <div>
                <div class="mb-3">
                    <div class="text-sm font-extrabold text-slate-900">
                        انتخاب نسخه محصول
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        متناسب با نیاز خود یکی از نسخه‌های قابل خرید را انتخاب کنید.
                    </div>
                </div>

                <div class="space-y-3">
                    <template x-for="saleItem in saleItems" :key="saleItem.id">
                        <button
                            type="button"
                            class="w-full cursor-pointer rounded-2xl border p-4 text-right transition disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isSubmitting"
                            :class="selectedSaleItemId === saleItem.id
                            ? 'border-brand-300 bg-brand-50 shadow-sm'
                            : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'"
                            @click="selectSaleItem(saleItem.id)"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="text-sm font-black text-slate-900"
                                        x-text="saleItem.title"
                                    ></span>

                                        <span
                                            x-show="selectedSaleItemId === saleItem.id"
                                            x-cloak
                                            class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-extrabold text-white"
                                        >
                                        انتخاب شده
                                    </span>
                                    </div>
                                </div>

                                <div
                                    class="shrink-0 whitespace-nowrap text-left text-sm font-black text-brand-700"
                                    x-text="formatPrice(saleItem.price)"
                                ></div>
                            </div>

                            <div
                                x-show="saleItem.description"
                                x-cloak
                                class="mt-2 text-xs text-justify leading-6 text-slate-500"
                                x-text="saleItem.description"
                            ></div>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        {{-- Simple product with addons: show base product price to avoid ambiguity --}}
        <template x-if="isSimpleProductWithAddons">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-sm font-extrabold text-slate-900">
                            قیمت محصول
                        </div>

                        <div class="mt-1 text-xs text-slate-500">
                            بدون محاسبه گزینه‌های همراه خرید
                        </div>
                    </div>

                    <div
                        class="shrink-0 whitespace-nowrap text-sm font-black text-brand-700"
                        x-text="formatPrice(selectedSaleItem?.price || 0)"
                    ></div>
                </div>
            </div>
        </template>

        {{-- Addons --}}
        <template x-if="hasAvailableSaleItems && addons.length > 0">
            <div :class="(shouldShowSaleItemSelector || isSimpleProductWithAddons) ? 'mt-6' : ''">
                <div class="mb-3">
                    <div class="text-sm font-extrabold text-slate-900">
                        گزینه‌های همراه خرید
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        این گزینه‌ها اختیاری هستند و همراه محصول اصلی به سبد خرید اضافه می‌شوند.
                    </div>
                </div>

                <div class="space-y-3">
                    <template x-for="addon in addons" :key="addon.id">
                        <label
                            class="block cursor-pointer rounded-2xl border p-4 transition"
                            :class="isSubmitting ? 'pointer-events-none border-slate-200 opacity-70' : (isAddonSelected(addon.id)
                                ? 'border-orange-200 bg-orange-50'
                                : 'border-slate-200 bg-white hover:bg-slate-50')"
                        >
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-60"
                                    :checked="isAddonSelected(addon.id)"
                                    :disabled="isSubmitting"
                                    @change="toggleAddon(addon.id)"
                                >

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        <div
                                            class="text-sm font-black text-slate-900"
                                            x-text="addon.title"
                                        ></div>

                                        <div
                                            class="shrink-0 whitespace-nowrap text-sm font-black text-brand-700"
                                            x-text="addon.price === 0 ? 'رایگان' : '+ ' + formatPrice(addon.price)"
                                        ></div>
                                    </div>

                                    <div
                                        x-show="addon.description"
                                        x-cloak
                                        class="mt-2 w-full text-xs leading-6 text-slate-500"
                                        x-text="addon.description"
                                    ></div>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
        </template>

        {{-- Total --}}
        <template x-if="hasAvailableSaleItems">
            <div
                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                :class="(shouldShowSaleItemSelector || addons.length > 0 || isSimpleProductWithAddons) ? 'mt-6' : ''"
            >
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-sm font-extrabold text-slate-800">
                            مبلغ قابل پرداخت
                        </div>
                    </div>

                    <div
                        class="shrink-0 whitespace-nowrap text-xl font-black text-brand-700"
                        x-text="formatPrice(totalPrice)"
                    ></div>
                </div>

                <div
                    x-show="selectedAddons.length > 0"
                    x-cloak
                    class="mt-3 border-t border-slate-200 pt-3"
                >
                    <div class="text-xs font-bold text-slate-500">
                        گزینه‌های انتخاب‌شده:
                    </div>

                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="addon in selectedAddons" :key="'selected-addon-' + addon.id">
                    <span
                        class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200"
                        x-text="addon.title"
                    ></span>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <div
            x-show="inCart"
            x-cloak
            class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-b from-white to-slate-50 shadow-sm ring-1 ring-slate-100"
            :class="isSubmitting ? 'pointer-events-none opacity-75' : ''"
        >
            <div class="relative p-3.5">
                {{-- Loading --}}
                <div
                    x-show="isSubmitting"
                    x-cloak
                    class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2 py-1 text-[10px] font-extrabold text-slate-500 ring-1 ring-slate-200 backdrop-blur"
                >
                    <span
                        class="h-3 w-3 animate-spin rounded-full border-2 border-slate-200 border-t-brand-500"></span>
                    <span>در حال اعمال...</span>
                </div>

                {{-- Header --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                        <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M20 6L9 17L4 12"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-black text-slate-900">
                            محصول در سبد خرید است
                        </div>
                    </div>
                </div>

                {{-- Quantity --}}
                <div
                    x-show="canChangeQuantity"
                    x-cloak
                    class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/80 px-3 py-2.5 ring-1 ring-slate-100"
                >
                    <div class="text-xs font-black text-slate-800">
                        تعداد
                    </div>

                    <div
                        class="flex shrink-0 items-center gap-1 rounded-2xl border border-slate-200 bg-slate-50 p-1">
                        <button
                            type="button"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-base font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="isSubmitting || quantity <= 1"
                            @click="decreaseQuantity()"
                        >
                            −
                        </button>

                        <div
                            class="flex h-8 min-w-10 items-center justify-center rounded-xl bg-white px-2 text-sm font-black text-slate-900 ring-1 ring-slate-100"
                            x-text="new Intl.NumberFormat('fa-IR').format(quantity)"
                        ></div>

                        <button
                            type="button"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-base font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="isSubmitting || !canIncreaseQuantity"
                            @click="increaseQuantity()"
                        >
                            +
                        </button>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a
                        href="{{ route('cart.index') }}"
                        class="inline-flex items-center justify-center [&_svg]:w-4 [&_svg]:h-4 gap-2 rounded-2xl bg-brand-500 px-3 py-2.5 text-xs font-extrabold text-white transition hover:bg-brand-600 active:bg-brand-700"
                    >
                        @include('icons.shopping-cart-02')

                        <span>سبد خرید</span>
                    </a>

                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center justify-center [&_svg]:w-4 [&_svg]:h-4 gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-extrabold text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSubmitting"
                        @click="removeFromCart()"
                    >
                        @include('icons.delete-02')

                        <span>حذف</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div
            x-show="hasAvailableSaleItems && !inCart"
            x-cloak
            class="mt-5 space-y-3"
        >
            <button
                type="button"
                class="w-full inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
                :disabled="!selectedSaleItemId || isSubmitting"
                @click="submit(true)"
            >
                <span x-show="!isSubmitting" x-cloak>
                    افزودن به سبد خرید
                </span>

                <span
                    x-show="isSubmitting"
                    x-cloak
                    class="inline-flex items-center gap-2"
                >
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <span>در حال افزودن...</span>
                </span>
            </button>
        </div>

        {{-- Modal --}}
        <div
            x-show="modalOpen"
            x-cloak
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-6 backdrop-blur-sm"
            @keydown.escape.window="closeModal()"
            @click.self="closeModal()"
        >
            <div
                x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-4"
                class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200"
            >
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                            :class="modal.type === 'success'
                        ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100'
                        : 'bg-rose-50 text-rose-600 ring-1 ring-rose-100'"
                        >
                            <template x-if="modal.type === 'success'">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M20 6L9 17L4 12"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </template>

                            <template x-if="modal.type === 'error'">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M12 8V12"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M12 16H12.01"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                    <path
                                        d="M10.29 3.86L1.82 18A2 2 0 0 0 3.53 21H20.47A2 2 0 0 0 22.18 18L13.71 3.86A2 2 0 0 0 10.29 3.86Z"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </template>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div
                                class="text-base font-black text-slate-900"
                                x-text="modal.title"
                            ></div>

                            <div
                                class="mt-1 text-xs leading-6 text-slate-500"
                                x-text="modal.message"
                            ></div>
                        </div>

                        <button
                            type="button"
                            class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            @click="closeModal()"
                        >
                            ✕
                        </button>
                    </div>

                    <div
                        x-show="modal.type === 'success'"
                        x-cloak
                        class="mt-5 grid grid-cols-2 gap-3"
                    >
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-bold text-slate-500">
                                محصولات سبد خرید
                            </div>

                            <div class="mt-2 flex items-end gap-1">
                        <span
                            class="text-2xl font-black text-slate-900"
                            x-text="new Intl.NumberFormat('fa-IR').format(modal.itemsCount)"
                        ></span>
                                <span class="pb-1 text-xs font-bold text-slate-500">
                            مورد
                        </span>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                            <div class="text-xs font-bold text-emerald-700">
                                مبلغ فعلی سبد
                            </div>

                            <div
                                class="mt-2 whitespace-nowrap text-base font-black text-emerald-700"
                                x-text="formatPrice(modal.totalAmount)"
                            ></div>
                        </div>
                    </div>

                    <div
                        x-show="modal.type === 'success' && modal.selectedAddons.length > 0"
                        x-cloak
                        class="mt-4 rounded-2xl border border-slate-200 bg-white p-4"
                    >
                        <div class="text-xs font-black text-slate-700">
                            گزینه‌های همراه انتخاب‌شده
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="addon in modal.selectedAddons" :key="'modal-addon-' + addon.id">
                        <span
                            class="rounded-full bg-slate-50 px-3 py-1 text-[11px] font-bold text-slate-600 ring-1 ring-slate-200"
                            x-text="addon.title"
                        ></span>
                            </template>
                        </div>
                    </div>

                    <div
                        x-show="modal.type === 'success' && modal.discountAmount > 0"
                        x-cloak
                        class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs font-bold text-emerald-700">
                                تخفیف اعمال‌شده
                            </div>

                            <div
                                class="whitespace-nowrap text-sm font-black text-emerald-700"
                                x-text="formatPrice(modal.discountAmount)"
                            ></div>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        <a
                            x-show="modal.type === 'success'"
                            x-cloak
                            :href="cartUrl"
                            class="flex w-full items-center justify-center gap-2 rounded-2xl bg-brand-500 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600"
                        >
                            مشاهده سبد خرید و ادامه خرید
                        </a>

                        <button
                            type="button"
                            class="w-full cursor-pointer rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50"
                            @click="closeModal()"
                            x-text="modal.type === 'success' ? 'ادامه مشاهده محصول' : 'متوجه شدم'"
                        ></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-front.cart>
