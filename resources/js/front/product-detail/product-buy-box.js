export default function productBuyBox(config) {
    return {
        productId: Number(config.productId),
        saleItems: config.saleItems ?? [],
        addons: config.addons ?? [],
        purchasePoints: config.purchasePoints ?? {enabled: false, items: {}},
        cartUrl: config.cartUrl,
        hasAvailableSaleItems: Boolean(config.hasAvailableSaleItems ?? false),

        selectedSaleItemId: null,
        selectedAddonIds: [],

        isSubmitting: false,
        isSyncingFromExternalCart: false,
        externalSyncTimeout: null,

        modalOpen: false,
        modal: {
            title: '',
            message: '',
            itemsCount: 0,
            subtotalAmount: 0,
            discountAmount: 0,
            amountBeforeTax: 0,
            selectedAddons: [],
            type: 'success',
        },

        purchaseState: config.purchaseState ?? {},

        inCart: false,
        quantity: 1,
        canChangeQuantity: false,
        maxQuantityPerOrder: null,
        originalSaleItemId: null,
        originalAddonIds: [],

        selectedSaleItemAnimationId: null,
        selectedSaleItemAnimationTimer: null,

        offerTimerInterval: null,

        offerCountdown: {
            days: '۰۰',
            hours: '۰۰',
            minutes: '۰۰',
            seconds: '۰۰',
        },

        offerProgressPercent: 100,
        offerExpired: false,
        offerUrgencyText: '',

        mainBuyBoxVisible: true,
        mainBuyBoxObserver: null,
        stickyBuyBarFallbackHandler: null,
        stickyBuyBarResizeHandler: null,

        playSaleItemSelectAnimation(id) {
            const itemId = Number(id);

            clearTimeout(this.selectedSaleItemAnimationTimer);

            this.selectedSaleItemAnimationId = null;

            this.$nextTick(() => {
                this.selectedSaleItemAnimationId = itemId;

                this.selectedSaleItemAnimationTimer = setTimeout(() => {
                    this.selectedSaleItemAnimationId = null;
                }, 260);
            });
        },

        init() {
            this.saleItems = this.saleItems.map((saleItem) => ({
                ...saleItem,
                id: Number(saleItem.id),
                price: Number(saleItem.price ?? 0),
                max_quantity_per_order: saleItem.max_quantity_per_order === null
                    ? null
                    : Number(saleItem.max_quantity_per_order),
            }));

            this.addons = this.addons.map((addon) => ({
                ...addon,
                id: Number(addon.id),
                price: Number(addon.price ?? 0),
                max_quantity_per_order: addon.max_quantity_per_order === null
                    ? null
                    : Number(addon.max_quantity_per_order),
            }));

            if (this.hasAvailableSaleItems && this.saleItems.length > 0) {
                this.selectedSaleItemId = Number(this.saleItems[0].id);
            }

            this.applyPurchaseState(this.purchaseState);

            this.$nextTick(() => {
                this.startSpecialOfferTimer();
                this.initStickyBuyBar();

                this.$watch('selectedSaleItemId', () => {
                    this.startSpecialOfferTimer();
                });
            });
        },

        destroy() {
            this.stopSpecialOfferTimer();
            this.destroyStickyBuyBar();
        },

        get shouldShowSaleItemSelector() {
            return this.saleItems.length > 1;
        },

        get isSimpleProduct() {
            return this.saleItems.length === 1;
        },

        get isSimpleProductWithAddons() {
            return this.isSimpleProduct && this.addons.length > 0;
        },

        get selectedSaleItem() {
            return this.saleItems.find(item => Number(item.id) === Number(this.selectedSaleItemId)) ?? null;
        },

        get selectedAddons() {
            return this.addons.filter((addon) => {
                return this.selectedAddonIds.includes(Number(addon.id));
            });
        },

        get totalPrice() {
            if (!this.hasAvailableSaleItems) {
                return null;
            }

            const saleItemPrice = Number(this.selectedSaleItem?.price ?? 0) * Number(this.quantity || 1);

            const addonsPrice = this.selectedAddons.reduce((total, addon) => {
                return total + Number(addon.price ?? 0);
            }, 0);

            return saleItemPrice + addonsPrice;
        },

        get shouldShowStickyBuyBar() {
            return Boolean(this.hasAvailableSaleItems && !this.mainBuyBoxVisible);
        },

        get stickyBuyActionLabel() {
            return this.inCart ? 'مشاهده و ویرایش خرید' : 'افزودن به سبد خرید';
        },

        get stickyPriceCaption() {
            if (this.inCart || this.selectedAddonIds.length > 0 || Number(this.quantity) > 1) {
                return 'مبلغ انتخاب فعلی';
            }

            return this.shouldShowSaleItemSelector ? 'مبلغ نسخه انتخاب شده' : 'مبلغ محصول';
        },

        initStickyBuyBar() {
            const buyBox = this.$refs.mainBuyBox;

            if (!buyBox) {
                return;
            }

            const updateVisibility = (isVisible) => {
                const normalizedVisibility = Boolean(isVisible);

                if (this.mainBuyBoxVisible === normalizedVisibility) {
                    return;
                }

                this.mainBuyBoxVisible = normalizedVisibility;

                this.$nextTick(() => {
                    this.notifyStickyBuyBarVisibility();
                });
            };

            if ('IntersectionObserver' in window) {
                this.mainBuyBoxObserver = new IntersectionObserver((entries) => {
                    const entry = entries[0];

                    updateVisibility(Boolean(entry?.isIntersecting));
                }, {
                    root: null,
                    threshold: 0,
                });

                this.mainBuyBoxObserver.observe(buyBox);
            } else {
                this.stickyBuyBarFallbackHandler = () => {
                    const rect = buyBox.getBoundingClientRect();
                    const isVisible = rect.bottom > 0 && rect.top < window.innerHeight;

                    updateVisibility(isVisible);
                };

                window.addEventListener('scroll', this.stickyBuyBarFallbackHandler, {passive: true});
                window.addEventListener('resize', this.stickyBuyBarFallbackHandler);

                this.stickyBuyBarFallbackHandler();
            }

            this.stickyBuyBarResizeHandler = () => {
                if (!this.shouldShowStickyBuyBar) {
                    return;
                }

                this.$nextTick(() => {
                    this.notifyStickyBuyBarVisibility();
                });
            };

            window.addEventListener('resize', this.stickyBuyBarResizeHandler);

            // Ensure dependent floating UI receives the initial state too.
            this.$nextTick(() => {
                this.notifyStickyBuyBarVisibility();
            });
        },

        destroyStickyBuyBar() {
            if (this.mainBuyBoxObserver) {
                this.mainBuyBoxObserver.disconnect();
                this.mainBuyBoxObserver = null;
            }

            if (this.stickyBuyBarFallbackHandler) {
                window.removeEventListener('scroll', this.stickyBuyBarFallbackHandler);
                window.removeEventListener('resize', this.stickyBuyBarFallbackHandler);
                this.stickyBuyBarFallbackHandler = null;
            }

            if (this.stickyBuyBarResizeHandler) {
                window.removeEventListener('resize', this.stickyBuyBarResizeHandler);
                this.stickyBuyBarResizeHandler = null;
            }

            window.dispatchEvent(new CustomEvent('product-sticky-buy-bar-visibility', {
                detail: {
                    visible: false,
                    height: 0,
                },
            }));
        },

        notifyStickyBuyBarVisibility() {
            const visible = this.shouldShowStickyBuyBar;
            const stickyBar = document.querySelector(`[data-product-sticky-buy-bar="${this.productId}"]`);
            const height = visible && stickyBar
                ? Math.ceil(stickyBar.getBoundingClientRect().height)
                : 0;

            window.dispatchEvent(new CustomEvent('product-sticky-buy-bar-visibility', {
                detail: {
                    visible,
                    height,
                },
            }));
        },

        scrollToMainBuyBox() {
            const buyBox = this.$refs.mainBuyBox;

            if (!buyBox) {
                return;
            }

            const inheritedHeaderOffset = Number.parseFloat(
                window.getComputedStyle(buyBox).getPropertyValue('--header-offset')
            );
            const headerOffset = Number.isFinite(inheritedHeaderOffset) ? inheritedHeaderOffset : 130;
            const targetTop = window.scrollY + buyBox.getBoundingClientRect().top - headerOffset - 16;
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            window.scrollTo({
                top: Math.max(0, targetTop),
                behavior: prefersReducedMotion ? 'auto' : 'smooth',
            });

            window.setTimeout(() => {
                buyBox.focus({preventScroll: true});
            }, prefersReducedMotion ? 0 : 420);
        },

        get canIncreaseQuantity() {
            if (!this.canChangeQuantity) {
                return false;
            }

            if (this.maxQuantityPerOrder === null) {
                return true;
            }

            return Number(this.quantity) < Number(this.maxQuantityPerOrder);
        },

        async selectSaleItem(saleItemId) {
            if (this.isSubmitting) {
                return;
            }

            const normalizedSaleItemId = Number(saleItemId);

            if (Number(this.selectedSaleItemId) === normalizedSaleItemId) {
                return;
            }

            const previousSaleItemId = this.selectedSaleItemId;

            this.selectedSaleItemId = normalizedSaleItemId;

            if (!this.inCart) {
                this.normalizeQuantityForSelectedSaleItem();

                return;
            }

            const result = await this.submit(false);

            if (!result?.success) {
                this.selectedSaleItemId = previousSaleItemId;
            }

            this.startSpecialOfferTimer();
        },

        async toggleAddon(addonId) {
            if (this.isSubmitting) {
                return;
            }

            const normalizedAddonId = Number(addonId);
            const previousAddonIds = [...this.selectedAddonIds];

            if (this.selectedAddonIds.includes(normalizedAddonId)) {
                this.selectedAddonIds = this.selectedAddonIds.filter((id) => {
                    return Number(id) !== normalizedAddonId;
                });
            } else {
                this.selectedAddonIds.push(normalizedAddonId);
            }

            if (!this.inCart) {
                return;
            }

            const result = await this.submit(false);

            if (!result?.success) {
                this.selectedAddonIds = previousAddonIds;
            }
        },

        isAddonSelected(addonId) {
            return this.selectedAddonIds.includes(Number(addonId));
        },

        async submit(showModal = true) {
            if (!this.hasAvailableSaleItems) {
                this.openErrorModal('این محصول در حال حاضر ناموجود است.');

                return {
                    success: false,
                    message: 'این محصول در حال حاضر ناموجود است.',
                };
            }

            if (!this.selectedSaleItemId || this.isSubmitting) {
                return {
                    success: false,
                    message: 'درخواست معتبر نیست.',
                };
            }

            this.normalizeQuantityForSelectedSaleItem();

            this.isSubmitting = true;

            try {
                const result = await this.$wire.addToCart(
                    this.selectedSaleItemId,
                    this.selectedAddonIds,
                    this.quantity,
                );

                if (!result?.success) {
                    this.openErrorModal(result?.message ?? 'به‌روزرسانی سبد خرید ناموفق بود.');

                    return result;
                }

                this.applyPurchaseState(result.state);

                this.originalSaleItemId = Number(this.selectedSaleItemId);
                this.originalAddonIds = [...this.selectedAddonIds];

                if (showModal) {
                    this.openSuccessModal(result);
                }

                return result;
            } catch (error) {
                const result = {
                    success: false,
                    message: 'خطایی هنگام به‌روزرسانی سبد خرید رخ داد.',
                };

                this.openErrorModal(result.message);

                return result;
            } finally {
                this.isSubmitting = false;
            }
        },

        async removeFromCart() {
            if (!this.inCart || this.isSubmitting) {
                return;
            }

            this.isSubmitting = true;

            try {
                const result = await this.$wire.removeFromCart();

                if (!result?.success) {
                    this.openErrorModal(result?.message ?? 'حذف محصول از سبد خرید ناموفق بود.');

                    return;
                }

                this.applyPurchaseState(result.state);
            } catch (error) {
                this.openErrorModal('خطایی هنگام حذف محصول از سبد خرید رخ داد.');
            } finally {
                this.isSubmitting = false;
            }
        },

        async increaseQuantity() {
            if (!this.canIncreaseQuantity || this.isSubmitting) {
                return;
            }

            const previousQuantity = this.quantity;

            this.quantity += 1;

            const result = await this.submit(false);

            if (!result?.success) {
                this.quantity = previousQuantity;
            }
        },

        async decreaseQuantity() {
            if (!this.canChangeQuantity || this.quantity <= 1 || this.isSubmitting) {
                return;
            }

            const previousQuantity = this.quantity;

            this.quantity -= 1;

            const result = await this.submit(false);

            if (!result?.success) {
                this.quantity = previousQuantity;
            }
        },

        handleExternalPurchaseStateRefreshed(event) {
            const detail = event?.detail ?? {};

            if (Number(detail.productId) !== Number(this.productId)) {
                return;
            }

            this.applyPurchaseState(detail.state ?? {});
            this.stopExternalSync();
        },

        applyPurchaseState(state) {
            this.purchaseState = state ?? {};

            this.inCart = Boolean(this.purchaseState.in_cart);

            if (this.inCart) {
                this.quantity = Number(this.purchaseState.quantity ?? 1);
                this.canChangeQuantity = Boolean(this.purchaseState.can_change_quantity);
                this.maxQuantityPerOrder = this.purchaseState.max_quantity_per_order === null
                    ? null
                    : Number(this.purchaseState.max_quantity_per_order);

                this.selectedSaleItemId = Number(this.purchaseState.sale_item_id);
                this.selectedAddonIds = (this.purchaseState.selected_addon_ids ?? []).map(Number);

                this.originalSaleItemId = Number(this.purchaseState.sale_item_id);
                this.originalAddonIds = [...this.selectedAddonIds];

                this.normalizeQuantityForSelectedSaleItem();

                return;
            }

            this.quantity = 1;
            this.canChangeQuantity = false;
            this.maxQuantityPerOrder = null;
            this.originalSaleItemId = null;
            this.originalAddonIds = [];

            if (this.hasAvailableSaleItems && this.saleItems.length > 0) {
                this.selectedSaleItemId = Number(this.saleItems[0].id);
            }

            this.selectedAddonIds = [];
        },

        normalizeQuantityForSelectedSaleItem() {
            const maxQuantity = this.selectedSaleItem?.max_quantity_per_order ?? null;

            if (maxQuantity === null) {
                return;
            }

            if (Number(this.quantity) > Number(maxQuantity)) {
                this.quantity = Number(maxQuantity);
            }

            if (Number(this.quantity) < 1) {
                this.quantity = 1;
            }
        },

        openSuccessModal(result) {
            const subtotalAmount = Number(result.cart?.subtotal_amount ?? 0);
            const discountAmount = Number(result.cart?.discount_amount ?? 0);
            const amountBeforeTax = Number(
                result.cart?.taxable_amount
                ?? Math.max(0, subtotalAmount - discountAmount),
            );

            this.modal = {
                type: 'success',
                title: 'محصول به سبد خرید اضافه شد',
                message: result.message ?? 'انتخاب شما با موفقیت در سبد خرید ذخیره شد.',
                itemsCount: result.cart?.items_count ?? 0,
                subtotalAmount,
                discountAmount,
                amountBeforeTax,
                selectedAddons: this.selectedAddons,
            };

            this.$dispatch('open-modal', 'product-cart-feedback');
        },

        openErrorModal(message) {
            this.modal = {
                type: 'error',
                title: 'عملیات ناموفق بود',
                message,
                itemsCount: 0,
                subtotalAmount: 0,
                discountAmount: 0,
                amountBeforeTax: 0,
                selectedAddons: [],
            };

            this.$dispatch('open-modal', 'product-cart-feedback');
        },

        closeModal() {
            this.$dispatch('close-modal', 'product-cart-feedback');
        },

        formatPrice(price) {
            const normalizedPrice = Number(price || 0);

            if (normalizedPrice <= 0) {
                return 'رایگان';
            }

            return new Intl.NumberFormat('fa-IR').format(normalizedPrice) + ' تومان';
        },

        startExternalSync() {
            if (this.isSubmitting) {
                return;
            }

            this.isSyncingFromExternalCart = true;

            clearTimeout(this.externalSyncTimeout);

            this.externalSyncTimeout = setTimeout(() => {
                this.isSyncingFromExternalCart = false;
            }, 8000);
        },

        stopExternalSync() {
            clearTimeout(this.externalSyncTimeout);

            this.externalSyncTimeout = null;
            this.isSyncingFromExternalCart = false;
        },

        get shouldShowSpecialOfferBox() {
            return Boolean(
                this.selectedSaleItem
                && this.selectedSaleItem.has_discount
                && this.selectedSaleItem.special_box
                && this.selectedSaleItem.special_box.show
            );
        },

        get shouldShowSpecialOfferTimer() {
            return Boolean(
                this.shouldShowSpecialOfferBox
                && Number(this.selectedSaleItem?.special_box?.ends_at_ts || 0) > 0
            );
        },

        get shouldShowSpecialOfferProgress() {
            return Boolean(
                this.shouldShowSpecialOfferTimer
                && Number(this.selectedSaleItem?.special_box?.starts_at_ts || 0) > 0
            );
        },

        startSpecialOfferTimer() {
            this.stopSpecialOfferTimer();
            this.updateSpecialOfferCountdown();

            this.offerTimerInterval = setInterval(() => {
                this.updateSpecialOfferCountdown();
            }, 1000);
        },

        stopSpecialOfferTimer() {
            if (this.offerTimerInterval) {
                clearInterval(this.offerTimerInterval);
                this.offerTimerInterval = null;
            }
        },

        updateSpecialOfferCountdown() {
            if (!this.shouldShowSpecialOfferTimer) {
                this.resetSpecialOfferCountdown();
                return;
            }

            const endTs = Number(this.selectedSaleItem?.special_box?.ends_at_ts || 0);

            if (!endTs) {
                this.resetSpecialOfferCountdown();
                return;
            }

            const nowTs = Date.now();
            const diff = endTs - nowTs;

            if (diff <= 0) {
                this.offerExpired = true;
                this.offerCountdown = {
                    days: '۰۰',
                    hours: '۰۰',
                    minutes: '۰۰',
                    seconds: '۰۰',
                };
                this.offerProgressPercent = 0;
                this.offerUrgencyText = 'این تخفیف به پایان رسیده است.';
                this.stopSpecialOfferTimer();
                return;
            }

            this.offerExpired = false;

            const totalSeconds = Math.floor(diff / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            this.offerCountdown = {
                days: this.toFaDigits(String(days).padStart(2, '0')),
                hours: this.toFaDigits(String(hours).padStart(2, '0')),
                minutes: this.toFaDigits(String(minutes).padStart(2, '0')),
                seconds: this.toFaDigits(String(seconds).padStart(2, '0')),
            };

            if (this.shouldShowSpecialOfferProgress) {
                this.updateOfferProgress(nowTs, endTs);
            } else {
                this.offerProgressPercent = 100;
            }
            this.updateOfferUrgencyText(totalSeconds);
        },

        updateOfferProgress(nowTs, endTs) {
            const startTs = Number(this.selectedSaleItem?.special_box?.starts_at_ts || 0);

            if (!startTs || !endTs || endTs <= startTs) {
                this.offerProgressPercent = 100;
                return;
            }

            const remaining = endTs - nowTs;
            const total = endTs - startTs;

            this.offerProgressPercent = Math.max(
                0,
                Math.min(100, Math.round((remaining / total) * 100))
            );
        },

        updateOfferUrgencyText(totalSeconds) {
            if (totalSeconds <= 3600) {
                this.offerUrgencyText = 'کمتر از یک ساعت تا پایان تخفیف باقی مانده.';
                return;
            }

            if (totalSeconds <= 86400) {
                this.offerUrgencyText = 'کمتر از یک روز تا پایان این پیشنهاد فرصت دارید.';
                return;
            }

            this.offerUrgencyText = '';
        },

        resetSpecialOfferCountdown() {
            this.offerExpired = false;
            this.offerProgressPercent = 100;
            this.offerUrgencyText = '';
            this.offerCountdown = {
                days: '۰۰',
                hours: '۰۰',
                minutes: '۰۰',
                seconds: '۰۰',
            };
        },

        toFaDigits(value) {
            return String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);
        },
    };
}
