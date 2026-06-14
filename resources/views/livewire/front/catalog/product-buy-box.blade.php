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
                            class="w-full cursor-pointer rounded-2xl border p-4 text-right transition"
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
                            :class="isAddonSelected(addon.id)
                            ? 'border-orange-200 bg-orange-50'
                            : 'border-slate-200 bg-white hover:bg-slate-50'"
                        >
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400"
                                    :checked="isAddonSelected(addon.id)"
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
            class="mt-5 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm"
        >
            <div class="p-4">
                {{-- Row 1: icon + title --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-sm">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M20 6L9 17L4 12"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div class="text-sm font-black leading-6 text-emerald-900">
                        این محصول در سبد خرید شماست
                    </div>
                </div>

                {{-- Row 2: full-width description --}}
                <div class="mt-3 text-xs leading-7 text-emerald-700">
                    انتخاب‌های فعلی شما ذخیره شده‌اند. می‌توانید نسخه محصول، گزینه‌های همراه یا تعداد را تغییر دهید.
                </div>

                {{-- Row 3: remove button --}}
                <div class="mt-4">
                    <button
                        type="button"
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-white px-4 py-2.5 text-xs font-extrabold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700 disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="isSubmitting"
                        @click="removeFromCart()"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M4 7H20"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <path
                                d="M10 11V17"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <path
                                d="M14 11V17"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                            <path
                                d="M6 7L7 20H17L18 7"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M9 7V4H15V7"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <span>حذف از سبد خرید</span>
                    </button>
                </div>
            </div>

            <div
                x-show="canChangeQuantity || hasUnsavedChanges"
                x-cloak
                class="border-t border-emerald-100 bg-white p-4"
            >
                <div class="space-y-3">
                    <div
                        x-show="canChangeQuantity"
                        x-cloak
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-3"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="text-xs font-black text-slate-800">
                                    تعداد خرید
                                </div>

                                <div class="mt-1 text-[11px] leading-5 text-slate-500">
                                    تعداد موردنظر برای همین محصول در سبد خرید
                                </div>
                            </div>

                            <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-1">
                                <button
                                    type="button"
                                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-lg font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="isSubmitting || quantity <= 1"
                                    @click="decreaseQuantity()"
                                >
                                    −
                                </button>

                                <div
                                    class="flex h-9 min-w-12 items-center justify-center rounded-xl bg-slate-50 px-3 text-sm font-black text-slate-900"
                                    x-text="new Intl.NumberFormat('fa-IR').format(quantity)"
                                ></div>

                                <button
                                    type="button"
                                    class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-xl text-lg font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40"
                                    :disabled="isSubmitting || !canIncreaseQuantity"
                                    @click="increaseQuantity()"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        x-show="hasUnsavedChanges"
                        x-cloak
                        class="rounded-2xl border border-amber-200 bg-amber-50 p-3"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
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

                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-black text-amber-800">
                                    تغییرات شما هنوز ذخیره نشده‌اند
                                </div>

                                <div class="mt-1 text-[11px] leading-5 text-amber-700">
                                    برای اعمال نسخه یا گزینه‌های همراه جدید، تغییرات سبد خرید را ذخیره کنید.
                                </div>
                            </div>
                        </div>
                    </div>

                    <button
                        x-show="hasUnsavedChanges"
                        x-cloak
                        type="button"
                        class="w-full cursor-pointer rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70"
                        :disabled="isSubmitting"
                        @click="submit(false)"
                    >
                <span
                    x-show="!isSubmitting"
                    class="inline-flex items-center justify-center gap-2"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M20 6L9 17L4 12"
                            stroke="currentColor"
                            stroke-width="2.3"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span>ذخیره تغییرات سبد خرید</span>
                </span>

                        <span
                            x-show="isSubmitting"
                            x-cloak
                            class="inline-flex items-center justify-center gap-2"
                        >
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                    <span>در حال ذخیره...</span>
                </span>
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
