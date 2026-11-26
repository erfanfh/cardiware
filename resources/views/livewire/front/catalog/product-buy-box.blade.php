<x-front.cart>
    <div
        x-data="productBuyBox({
            productId: @js($product->id),
            saleItems: @js($saleItems),
            addons: @js($addons),
            purchasePoints: @js($purchasePoints),
            purchaseState: @js($purchaseState),
            hasAvailableSaleItems: @js($hasAvailableSaleItems),
            cartUrl: @js(route('cart.index')),
        })"
        x-on:product-buy-box-sync-started.window="startExternalSync()"
        x-on:product-buy-box-state-refreshed.window="handleExternalPurchaseStateRefreshed($event)"
    >
        <div
            id="product-main-buy-box"
            x-ref="mainBuyBox"
            tabindex="-1"
            class="relative outline-none"
            :class="isSyncingFromExternalCart ? 'pointer-events-none opacity-75' : ''"
        >
            <div
                x-show="isSyncingFromExternalCart"
                x-cloak
                class="absolute inset-0 z-30 flex items-center justify-center rounded-2xl bg-white/70 dark:bg-slate-900/70 backdrop-blur-[2px]"
            >
                <div
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-extrabold text-slate-600 dark:text-slate-300 shadow-sm"
                >
                    <span
                        class="h-4 w-4 animate-spin rounded-full border-2 border-slate-200 dark:border-slate-600 border-t-brand-500"></span>
                    <span>Updating cart...</span>
                </div>
            </div>

            <template x-if="!hasAvailableSaleItems">
                <div class="rounded-2xl border border-rose-200 dark:border-rose-900 bg-rose-50 dark:bg-rose-950/20 p-4">
                    <div class="flex items-start gap-3">

                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-900 text-rose-600 dark:text-rose-300">
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
                            <div class="text-sm font-black text-rose-900 dark:text-rose-200">
                                Unavailable
                            </div>

                            <div class="mt-1 text-xs leading-6 text-rose-700 dark:text-rose-300">
                                There are no active versions of this product available to purchase.
                            </div>
                        </div>

                    </div>
                </div>
            </template>

            {{-- Version selector: only for variable products --}}
            <template x-if="shouldShowSaleItemSelector">
                <div>
                    <div class="mb-3">
                        <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            Select product version
                        </div>

                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Choose a version that suits your needs.
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="saleItem in saleItems" :key="saleItem.id">
                            <button
                                type="button"
                                class="w-full transform-gpu cursor-pointer rounded-2xl border p-4 text-start transition-all duration-200 ease-out disabled:cursor-not-allowed disabled:opacity-60 motion-reduce:transition-none"
                                :disabled="isSubmitting"
                                :class="[
                                    selectedSaleItemId === Number(saleItem.id)
                                        ? 'border-brand-300 bg-brand-50 shadow-sm ring-2 ring-brand-200/70 dark:border-brand-500/40 dark:bg-brand-900/20 dark:ring-brand-500/20'
                                        : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800',

                                    selectedSaleItemAnimationId === Number(saleItem.id)
                                        ? 'scale-[1.015] -translate-y-0.5'
                                        : 'scale-100 translate-y-0'
                                ]"
                                @click="
                                    selectSaleItem(saleItem.id);
                                    playSaleItemSelectAnimation(saleItem.id);
                                "
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span
                                                class="text-sm font-black text-slate-900 dark:text-slate-100"
                                                x-text="saleItem.title"
                                            ></span>

                                            <span
                                                x-show="selectedSaleItemId === Number(saleItem.id)"
                                                x-cloak
                                                class="rounded-full bg-brand-600 px-2 py-0.5 text-[10px] font-extrabold text-white"
                                            >
                                                Selected
                                            </span>

                                            <span
                                                x-show="saleItem.vip_access_enabled"
                                                x-cloak
                                                class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-extrabold text-violet-700 dark:bg-violet-500/15 dark:text-violet-300"
                                            >With subscription VIP</span>
                                        </div>
                                    </div>

                                    <div class="shrink-0 text-end">
                                        <div
                                            x-show="saleItem.has_discount"
                                            x-cloak
                                            class="mb-1 flex items-center justify-end gap-1.5"
                                        >
                                            <span
                                                class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-black text-brand-600 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-900/40"
                                                x-text="new Intl.NumberFormat('en-US').format(saleItem.discount_percent) + '% off'"
                                            ></span>

                                            <span
                                                class="text-[11px] font-bold text-slate-400 line-through decoration-brand-400/70 decoration-2 dark:text-slate-500"
                                                x-text="formatPrice(saleItem.regular_price)"
                                            ></span>
                                        </div>

                                        <div
                                            class="whitespace-nowrap text-base font-black text-brand-700 dark:text-brand-400"
                                            x-text="formatPrice(saleItem.price)"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    x-show="saleItem.description"
                                    x-cloak
                                    class="mt-2 text-xs text-justify leading-6 text-slate-500 dark:text-slate-400"
                                    x-text="saleItem.description"
                                ></div>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Simple product with addons: show base product price to avoid ambiguity --}}
            <template x-if="isSimpleProductWithAddons">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-4"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                                    Product price
                                </div>

                                <span
                                    x-show="selectedSaleItem?.has_discount"
                                    x-cloak
                                    class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-black text-brand-600 ring-1 ring-brand-100 dark:bg-brand-900/30 dark:text-brand-300 dark:ring-brand-900/40"
                                    x-text="new Intl.NumberFormat('en-US').format(selectedSaleItem?.discount_percent || 0) + '% off'"
                                ></span>
                            </div>

                            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Excluding purchase add-ons
                            </div>
                        </div>

                        <div class="shrink-0 text-end">
                            <div
                                x-show="selectedSaleItem?.has_discount"
                                x-cloak
                                class="mb-1 whitespace-nowrap text-[0.7rem] font-bold text-slate-400 line-through decoration-brand-400/70 decoration-2 dark:text-slate-500"
                                x-text="formatPrice(selectedSaleItem?.regular_price || 0)"
                            ></div>

                            <div
                                class="whitespace-nowrap text-sm font-black text-brand-700 dark:text-brand-400"
                                x-text="formatPrice(selectedSaleItem?.price || 0)"
                            ></div>
                        </div>
                    </div>
                </div>
            </template>

            <section
                x-show="selectedSaleItem?.vip_access_enabled"
                x-data="{ claimedVipIds: @js($claimedVipSaleItemIds), ownedIds: @js($ownedSaleItemIds) }"
                x-cloak
                class="mt-5 rounded-2xl border border-violet-200 bg-violet-50/70 p-4 dark:border-violet-500/25 dark:bg-violet-500/5"
                aria-label="Subscription access VIP"
            >
                <div class="flex flex-col gap-4">
                    <div class="space-y-1.5 text-start">
                        <div class="text-sm font-black text-violet-900 dark:text-violet-200">Subscription access VIP</div>
                        <p class="text-xs leading-6 text-slate-600 dark:text-slate-400">Add this version to your downloads at no extra cost.</p>
                    </div>
                    @if($hasActiveVip)
                        <button type="button" x-show="!claimedVipIds.includes(Number(selectedSaleItemId)) && !ownedIds.includes(Number(selectedSaleItemId))" x-cloak x-on:click="$wire.claimVipSaleItem(Number(selectedSaleItemId))" wire:loading.attr="disabled" wire:target="claimVipSaleItem" class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-violet-700 px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-violet-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-500 disabled:opacity-60">
                            Add to downloads
                        </button>
                        <a x-show="claimedVipIds.includes(Number(selectedSaleItemId)) || ownedIds.includes(Number(selectedSaleItemId))" x-cloak :href="'{{ route('account.downloads') }}' + (ownedIds.includes(Number(selectedSaleItemId)) ? '' : '?vipItem=' + selectedSaleItemId)" class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 py-2.5 text-sm font-extrabold text-violet-800 transition hover:border-violet-300 hover:bg-violet-100 dark:border-violet-500/25 dark:bg-slate-900 dark:text-violet-200 dark:hover:bg-violet-500/10">
                            View downloads <span aria-hidden="true">←</span>
                        </a>
                    @else
                        <a href="{{ route('vip.index') }}" class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-violet-200 bg-white px-4 py-2.5 text-sm font-extrabold text-violet-800 transition hover:border-violet-300 hover:bg-violet-100 dark:border-violet-500/25 dark:bg-slate-900 dark:text-violet-200 dark:hover:bg-violet-500/10">
                            View plans VIP <span aria-hidden="true">←</span>
                        </a>
                    @endif
                </div>
            </section>

            <template x-if="shouldShowSpecialOfferBox">
                <section class="mt-4">
                    <div
                        class="relative overflow-hidden rounded-3xl border border-orange-200 bg-gradient-to-br from-orange-50 via-white to-rose-50 p-3.5 shadow-sm ring-1 ring-orange-100/70 dark:border-orange-900/40 dark:from-slate-900 dark:via-slate-950 dark:to-orange-950/20 dark:ring-orange-900/20"
                    >
                        {{-- Decorative --}}
                        <div
                            class="pointer-events-none absolute -left-8 -top-8 h-24 w-24 rounded-full bg-orange-300/30 blur-3xl dark:bg-orange-500/10"></div>
                        <div
                            class="pointer-events-none absolute -bottom-10 -right-8 h-24 w-24 rounded-full bg-rose-300/25 blur-3xl dark:bg-rose-500/10"></div>

                        <div class="relative">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-gradient-to-l from-rose-500 to-orange-500 px-2.5 py-1 text-[10px] font-black text-white shadow-sm"
                                        >
                                            <span class="[&_svg]:w-3 [&_svg]:h-3 text-orange-100">
                                                @include('icons.fire')
                                            </span>
                                            <span>Special offer</span>
                                        </span>
                                    </div>

                                    <h3
                                        class="mt-3 text-sm font-black leading-6 text-slate-900 dark:text-white"
                                        x-text="selectedSaleItem?.special_box?.title || 'Limited-time special discount'"
                                    ></h3>

                                    <p
                                        x-show="selectedSaleItem?.special_box?.description"
                                        x-cloak
                                        class="mt-1.5 text-xs leading-6 text-slate-600 dark:text-slate-300"
                                        x-text="selectedSaleItem?.special_box?.description"
                                    ></p>
                                </div>
                            </div>

                            {{-- Timer --}}
                            <div
                                x-show="shouldShowSpecialOfferTimer"
                                x-cloak
                                class="mt-3 rounded-2xl border border-white/80 bg-white/75 p-3 shadow-sm ring-1 ring-slate-100 backdrop-blur dark:border-white/10 dark:bg-white/[0.04] dark:ring-white/10"
                            >
                                <div class="mb-2 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-300">
                                        End time
                                    </span>

                                    <span
                                        x-show="selectedSaleItem?.special_box?.ends_at_label"
                                        x-cloak
                                        class="text-[10px] font-bold text-slate-400 dark:text-slate-500"
                                        x-text="selectedSaleItem?.special_box?.ends_at_label"
                                    ></span>
                                </div>

                                <div class="grid grid-cols-4 gap-1.5">
                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-1 py-2 text-center dark:border-white/10 dark:bg-slate-950/70">
                                        <div class="text-sm font-black text-slate-900 dark:text-white"
                                             x-text="offerCountdown.seconds"></div>
                                        <div class="mt-0.5 text-[9px] font-bold text-slate-400">seconds</div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-1 py-2 text-center dark:border-white/10 dark:bg-slate-950/70">
                                        <div class="text-sm font-black text-slate-900 dark:text-white"
                                             x-text="offerCountdown.minutes"></div>
                                        <div class="mt-0.5 text-[9px] font-bold text-slate-400">minutes</div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-1 py-2 text-center dark:border-white/10 dark:bg-slate-950/70">
                                        <div class="text-sm font-black text-slate-900 dark:text-white"
                                             x-text="offerCountdown.hours"></div>
                                        <div class="mt-0.5 text-[9px] font-bold text-slate-400">hours</div>
                                    </div>

                                    <div
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-1 py-2 text-center dark:border-white/10 dark:bg-slate-950/70">
                                        <div class="text-sm font-black text-slate-900 dark:text-white"
                                             x-text="offerCountdown.days"></div>
                                        <div class="mt-0.5 text-[9px] font-bold text-slate-400">days</div>
                                    </div>
                                </div>

                                {{-- Progress --}}
                                <div
                                    x-show="shouldShowSpecialOfferProgress"
                                    x-cloak
                                    class="mt-3"
                                >
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">
                                            Discount time remaining
                                        </span>

                                        <span
                                            class="text-[10px] font-black text-orange-600 dark:text-orange-300"
                                            x-text="new Intl.NumberFormat('en-US').format(offerProgressPercent) + '%'"
                                        ></span>
                                    </div>

                                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                                        <div
                                            class="h-full rounded-full bg-gradient-to-l from-brand-500 via-brand-600 to-rose-600 shadow-[0_0_14px_rgba(244,63,94,0.35)] transition-all duration-500"
                                            :style="`width: ${offerProgressPercent}%`"
                                        ></div>
                                    </div>
                                </div>

                                <div
                                    x-show="offerUrgencyText"
                                    x-cloak
                                    class="mt-2 rounded-xl bg-orange-50 px-2.5 py-2 text-[10px] font-bold text-orange-700 ring-1 ring-orange-100 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/10"
                                    x-text="offerUrgencyText"
                                ></div>

                                <div
                                    x-show="offerExpired"
                                    x-cloak
                                    class="mt-2 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-center text-[11px] font-bold text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
                                >
                                    This offer has ended.
                                </div>
                            </div>

                            {{-- No timer fallback --}}
                            <div
                                x-show="!shouldShowSpecialOfferTimer"
                                x-cloak
                                class="mt-3 rounded-2xl border border-orange-100 bg-white/70 px-3 py-2 text-xs font-bold leading-6 text-orange-700 ring-1 ring-orange-100 dark:border-orange-500/10 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/10"
                            >
                                This special offer is available for a limited time.
                            </div>
                        </div>
                    </div>
                </section>
            </template>

            {{-- Addons --}}
            <template x-if="hasAvailableSaleItems && addons.length > 0">
                <div :class="(shouldShowSaleItemSelector || isSimpleProductWithAddons) ? 'mt-6' : ''">
                    <div class="mb-3">
                        <div class="text-sm font-extrabold text-slate-900 dark:text-slate-100">
                            Purchase add-ons
                        </div>

                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            These options are optional and are added to the cart with the main product.
                        </div>
                    </div>

                    <div class="space-y-3">
                        <template x-for="addon in addons" :key="addon.id">
                            <label
                                class="relative isolate block cursor-pointer overflow-hidden rounded-2xl border p-4 transition-all duration-200"
                                :class="[
                                    isSubmitting
                                        ? 'pointer-events-none opacity-70'
                                        : '',
                                    isAddonSelected(addon.id)
                                        ? 'border-brand-200 bg-brand-50 ring-2 ring-brand-100 dark:border-brand-500/30 dark:bg-brand-900/20 dark:ring-brand-500/10'
                                        : 'border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:hover:bg-slate-800'
                                ]"
                            >
                                <!-- background icon -->
                                <div
                                    aria-hidden="true"
                                    class="pointer-events-none absolute -bottom-5 -left-5 z-0 transition-all duration-200"
                                    :class="isAddonSelected(addon.id)
                                        ? 'text-orange-200/70 dark:text-orange-500/10 scale-105'
                                        : 'text-slate-200/60 dark:text-slate-700/30'"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-28 w-28"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M21 8.5 12 3 3 8.5"/>
                                        <path d="M21 8.5v7L12 21l-9-5.5v-7"/>
                                        <path d="M12 12.5 3 8.5"/>
                                        <path d="M12 12.5 21 8.5"/>
                                        <path d="M12 12.5V21"/>
                                    </svg>
                                </div>

                                <div class="relative z-10 flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400 disabled:cursor-not-allowed disabled:opacity-60 accent-brand-500"
                                        :checked="isAddonSelected(addon.id)"
                                        :disabled="isSubmitting"
                                        @change="toggleAddon(addon.id)"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-3">
                                            <div
                                                class="text-sm font-black text-slate-900 dark:text-slate-100"
                                                x-text="addon.title"
                                            ></div>

                                            <div
                                                class="shrink-0 whitespace-nowrap text-sm font-black text-brand-700 dark:text-brand-400"
                                                x-text="addon.price === 0 ? 'Free' : '+ ' + formatPrice(addon.price)"
                                            ></div>
                                        </div>

                                        <div
                                            x-show="addon.description"
                                            x-cloak
                                            class="mt-2 w-full text-xs leading-6 text-slate-500 dark:text-slate-400"
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
                    class="rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-900 p-4"
                    :class="(shouldShowSaleItemSelector || addons.length > 0 || isSimpleProductWithAddons || selectedSaleItem?.vip_access_enabled) ? 'mt-6' : ''"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-extrabold text-slate-800 dark:text-slate-100">
                                Amount due
                            </div>
                        </div>

                        <div
                            class="shrink-0 whitespace-nowrap text-xl font-black text-brand-700 dark:text-brand-400"
                            x-text="formatPrice(totalPrice)"
                        ></div>
                    </div>

                    <div
                        x-show="selectedSaleItem?.has_discount"
                        x-cloak
                        class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-200 pt-3 dark:border-slate-700"
                    >
                        <div
                            class="rounded-2xl bg-white p-3 ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-700">
                            <div class="text-[10px] font-black text-slate-400 dark:text-slate-500">
                                Original price
                            </div>

                            <div
                                class="mt-1 whitespace-nowrap text-xs font-black text-slate-500 line-through decoration-brand-400/70 decoration-2 dark:text-slate-400"
                                x-text="formatPrice((selectedSaleItem?.regular_price || 0) * quantity)"
                            ></div>
                        </div>

                        <div
                            class="rounded-2xl bg-emerald-50 p-3 ring-1 ring-emerald-100 dark:bg-emerald-950/20 dark:ring-emerald-900/40">
                            <div class="text-[10px] font-black text-emerald-600 dark:text-emerald-400">
                                Your savings
                            </div>

                            <div
                                class="mt-1 whitespace-nowrap text-xs font-black text-emerald-700 dark:text-emerald-300"
                                x-text="formatPrice((selectedSaleItem?.discount_amount || 0) * quantity)"
                            ></div>
                        </div>
                    </div>

                    <div
                        x-show="selectedAddons.length > 0"
                        x-cloak
                        class="mt-3 border-t border-slate-200 dark:border-slate-700 pt-3"
                    >
                        <div class="text-xs font-bold text-slate-500 dark:text-slate-400">
                            Selected options:
                        </div>

                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="addon in selectedAddons" :key="'selected-addon-' + addon.id">
                    <span
                        class="rounded-full bg-white dark:bg-slate-800 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700"
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
                class="mt-5 overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-gradient-to-b from-white to-slate-50 dark:from-slate-900 dark:to-slate-950 shadow-sm ring-1 ring-slate-100 dark:ring-slate-800"
                :class="isSubmitting ? 'pointer-events-none opacity-75' : ''"
            >
                <div class="relative p-3.5">
                    {{-- Loading --}}
                    <div
                        x-show="isSubmitting"
                        x-cloak
                        class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 dark:bg-slate-800/90 px-2 py-1 text-[10px] font-extrabold text-slate-500 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700 backdrop-blur"
                    >
            <span
                class="h-3 w-3 animate-spin rounded-full border-2 border-slate-200 dark:border-slate-600 border-t-brand-500"
            ></span>
                        <span>Applying...</span>
                    </div>

                    {{-- Header --}}
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 ring-1 ring-brand-100 dark:ring-brand-900"
                        >
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
                            <div class="text-sm font-black text-slate-900 dark:text-slate-100">
                                Product is in your cart
                            </div>
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div
                        x-show="canChangeQuantity"
                        x-cloak
                        class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/80 dark:bg-slate-900/60 px-3 py-2.5 ring-1 ring-slate-100 dark:ring-slate-800"
                    >
                        <div class="text-xs font-black text-slate-800 dark:text-slate-200">
                            Quantity
                        </div>

                        <div
                            class="flex shrink-0 items-center gap-1 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-1"
                        >
                            <button
                                type="button"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-base font-black text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="isSubmitting || quantity <= 1"
                                @click="decreaseQuantity()"
                            >
                                −
                            </button>

                            <div
                                class="flex h-8 min-w-10 items-center justify-center rounded-xl bg-white dark:bg-slate-950 px-2 text-sm font-black text-slate-900 dark:text-slate-100 ring-1 ring-slate-100 dark:ring-slate-800"
                                x-text="new Intl.NumberFormat('en-US').format(quantity)"
                            ></div>

                            <button
                                type="button"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl text-base font-black text-slate-700 dark:text-slate-200 transition hover:bg-slate-100 dark:hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="isSubmitting || !canIncreaseQuantity"
                                @click="increaseQuantity()"
                            >
                                +
                            </button>
                        </div>
                    </div>

                    <div
                        x-show="canChangeQuantity && maxQuantityPerOrder !== null"
                        x-cloak
                        class="mt-2 text-[11px] font-bold text-slate-400 dark:text-slate-500"
                    >
                        Maximum allowed quantity for this product:
                        <span x-text="new Intl.NumberFormat('en-US').format(maxQuantityPerOrder)"></span>
                        units
                    </div>

                    {{-- Actions --}}
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <a
                            href="{{ route('cart.index') }}"
                            class="inline-flex items-center justify-center [&_svg]:w-4 [&_svg]:h-4 gap-2 rounded-2xl bg-brand-500 dark:bg-brand-600 px-3 py-2.5 text-xs font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-700 active:bg-brand-700"
                        >
                            @include('icons.shopping-cart-02')
                            <span>Shopping cart</span>
                        </a>

                        <button
                            type="button"
                            class="inline-flex cursor-pointer items-center justify-center [&_svg]:w-4 [&_svg]:h-4 gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2.5 text-xs font-extrabold text-slate-500 dark:text-slate-300 transition hover:border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/20 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="isSubmitting"
                            @click="removeFromCart()"
                        >
                            @include('icons.delete-02')
                            <span>Remove</span>
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
                    class="w-full inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl bg-brand-500 dark:bg-brand-600 px-5 py-3 text-sm font-extrabold text-white shadow-soft transition hover:bg-brand-600 dark:hover:bg-brand-700 active:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="!selectedSaleItemId || isSubmitting"
                    @click="submit(true)"
                >
                    <span x-show="!isSubmitting" x-cloak>
                        Add to cart
                    </span>

                    <span
                        x-show="isSubmitting"
                        x-cloak
                        class="inline-flex items-center gap-2"
                    >
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                        <span>Adding...</span>
                    </span>
                </button>

                <div
                    x-show="purchasePoints.enabled && selectedSaleItemId && purchasePoints.items[selectedSaleItemId] > 0"
                    x-cloak
                    class="flex items-center gap-3 rounded-2xl border border-amber-200 bg-gradient-to-l from-amber-50 to-white px-4 py-3 dark:border-amber-900/50 dark:from-amber-950/30 dark:to-slate-900"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 2C7.58 2 4 3.79 4 6V18C4 20.21 7.58 22 12 22C16.42 22 20 20.21 20 18V6C20 3.79 16.42 2 12 2Z"
                                stroke="currentColor" stroke-width="2"/>
                            <path d="M4 6C4 8.21 7.58 10 12 10C16.42 10 20 8.21 20 6" stroke="currentColor"
                                  stroke-width="2"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-black text-amber-900 dark:text-amber-200 leading-6">
                            By purchasing this product
                            <span x-text="purchasePoints.items[selectedSaleItemId]"></span>
                            points will be added to your Community Club account.
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($actionLinks))
                <div class="mt-3 space-y-2 border-t border-slate-200 pt-3 dark:border-slate-700">
                    @foreach($actionLinks as $link)
                        <a
                            href="{{ $link['url'] }}"
                            @if($link['open_in_new_tab'])
                                target="_blank"
                            rel="noopener noreferrer"
                            @endif
                            class="group flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3.5 py-3 text-start shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-brand-200 hover:bg-brand-50/60 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-brand-200 focus:ring-offset-2 focus:ring-offset-white dark:border-slate-700 dark:bg-slate-900 dark:hover:border-brand-500/30 dark:hover:bg-brand-950/20 dark:focus:ring-brand-500/30 dark:focus:ring-offset-slate-900"
                            @if($link['open_in_new_tab'])
                                aria-label="{{ $link['title'] }}; opens in a new tab"
                            @endif
                        >
                            <span
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition group-hover:bg-white group-hover:text-brand-600 group-hover:shadow-sm dark:bg-slate-800 dark:text-slate-400 dark:group-hover:bg-slate-800 dark:group-hover:text-brand-300">
                                <svg
                                    class="size-4.5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M10.5 6H6.75A2.25 2.25 0 0 0 4.5 8.25v9A2.25 2.25 0 0 0 6.75 19.5h9A2.25 2.25 0 0 0 18 17.25V13.5"
                                    />
                                </svg>
                            </span>

                            <span class="min-w-0 flex-1">
                                <span
                                    class="block break-words text-sm font-extrabold text-slate-800 transition group-hover:text-brand-700 dark:text-slate-100 dark:group-hover:text-brand-300">
                                    {{ $link['title'] }}
                                </span>
                            </span>

                            <svg
                                class="size-4 shrink-0 text-slate-400 transition duration-200 group-hover:-translate-x-1 group-hover:text-brand-600 dark:text-slate-500 dark:group-hover:text-brand-300"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m15 18-6-6 6-6"
                                />
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @include('livewire.front.catalog.partials.product-sticky-buy-bar')

        {{-- Modal --}}
        <x-front.modal
            name="product-cart-feedback"
            max-width="max-w-md"
            title="Cart status"
            description="Result of adding or updating the product in your cart"
        >
            <div>
                {{-- Status --}}
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl
        dark:bg-slate-900 dark:ring-slate-700"
                        :class="modal.type === 'success'
            ? 'bg-brand-50 text-brand-600 ring-1 ring-brand-100 dark:bg-brand-950/30 dark:text-brand-400 dark:ring-brand-900'
            : 'bg-rose-50 text-rose-600 ring-1 ring-rose-100 dark:bg-rose-950/30 dark:text-rose-400 dark:ring-rose-900'"
                    >
                        <template x-if="modal.type === 'success'">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M20 6L9 17L4 12"
                                    stroke="currentColor"
                                    stroke-width="2.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </template>

                        <template x-if="modal.type === 'error'">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
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
                            class="text-base font-black text-slate-900 dark:text-slate-100"
                            x-text="modal.title"
                        ></div>

                        <div
                            class="mt-1.5 text-xs leading-6 text-slate-500 dark:text-slate-400"
                            x-text="modal.message"
                        ></div>
                    </div>
                </div>

                {{-- Success Summary --}}
                <div
                    x-show="modal.type === 'success'"
                    x-cloak
                    class="mt-5 space-y-3"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-4">
                            <div class="text-[11px] font-extrabold text-slate-500 dark:text-slate-400">
                                Item count
                            </div>

                            <div class="mt-2 flex items-end gap-1">
                <span
                    class="text-2xl font-black text-slate-900 dark:text-slate-100"
                    x-text="new Intl.NumberFormat('en-US').format(modal.itemsCount)"
                ></span>

                                <span class="pb-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                    items
                </span>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl border border-brand-100 dark:border-brand-900/40 bg-brand-50 dark:bg-transparent dark:bg-brand-950/30 p-4">
                            <div class="text-[11px] font-extrabold text-brand-700 dark:text-brand-400">
                                Cart amount
                            </div>

                            <div
                                class="mt-2 whitespace-nowrap text-base font-black text-brand-700 dark:text-brand-400"
                                x-text="formatPrice(modal.amountBeforeTax)"
                            ></div>
                        </div>
                    </div>

                    <div
                        x-show="modal.selectedAddons.length > 0"
                        x-cloak
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4"
                    >
                        <div class="text-xs font-black text-slate-700 dark:text-slate-200">
                            Selected add-on options
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="addon in modal.selectedAddons" :key="'modal-addon-' + addon.id">
                <span
                    class="rounded-full bg-slate-50 dark:bg-slate-800 px-3 py-1 text-[11px] font-bold text-slate-600 dark:text-slate-300 ring-1 ring-slate-200 dark:ring-slate-700"
                    x-text="addon.title"
                ></span>
                            </template>
                        </div>
                    </div>

                    <div
                        x-show="modal.discountAmount > 0"
                        x-cloak
                        class="rounded-2xl border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-950/30 p-4"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-xs font-extrabold text-emerald-700 dark:text-emerald-400">
                                Applied discount
                            </div>

                            <div
                                class="whitespace-nowrap text-sm font-black text-emerald-700 dark:text-emerald-400"
                                x-text="formatPrice(modal.discountAmount)"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="grid gap-3" :class="modal.type === 'success' ? 'grid-cols-2' : 'grid-cols-1'">
                    <a
                        x-show="modal.type === 'success'"
                        x-cloak
                        :href="cartUrl"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-brand-500 dark:bg-brand-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-brand-600 dark:hover:bg-brand-700 active:bg-brand-700"
                    >
                        View cart
                    </a>

                    <button
                        type="button"
                        class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-5 py-3 text-sm font-extrabold text-slate-700 dark:text-slate-200 transition hover:bg-slate-50 dark:hover:bg-slate-800"
                        @click="closeModal()"
                        x-text="modal.type === 'success' ? 'Continue viewing product' : 'Got it'"
                    ></button>
                </div>
            </x-slot:footer>
        </x-front.modal>
    </div>
</x-front.cart>
