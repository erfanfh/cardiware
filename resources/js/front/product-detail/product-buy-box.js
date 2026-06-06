export default function productBuyBox(config) {
    return {
        saleItems: config.saleItems ?? [],
        addons: config.addons ?? [],
        cartUrl: config.cartUrl,

        selectedSaleItemId: null,
        selectedAddonIds: [],

        isSubmitting: false,

        modalOpen: false,
        modal: {
            title: '',
            message: '',
            itemsCount: 0,
            subtotalAmount: 0,
            discountAmount: 0,
            totalAmount: 0,
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

        init() {
            if (this.saleItems.length > 0) {
                this.selectedSaleItemId = Number(this.saleItems[0].id);
            }

            this.applyPurchaseState(this.purchaseState);

            this.$watch('modalOpen', (isOpen) => {
                document.body.classList.toggle('overflow-hidden', isOpen);
            });
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
            return this.saleItems.find((saleItem) => {
                return Number(saleItem.id) === Number(this.selectedSaleItemId);
            }) ?? null;
        },

        get selectedAddons() {
            return this.addons.filter((addon) => {
                return this.selectedAddonIds.includes(Number(addon.id));
            });
        },

        get totalPrice() {
            const saleItemPrice = Number(this.selectedSaleItem?.price ?? 0) * Number(this.quantity || 1);

            const addonsPrice = this.selectedAddons.reduce((total, addon) => {
                return total + Number(addon.price ?? 0);
            }, 0);

            return saleItemPrice + addonsPrice;
        },

        selectSaleItem(saleItemId) {
            this.selectedSaleItemId = Number(saleItemId);
        },

        toggleAddon(addonId) {
            const normalizedAddonId = Number(addonId);

            if (this.selectedAddonIds.includes(normalizedAddonId)) {
                this.selectedAddonIds = this.selectedAddonIds.filter((id) => {
                    return Number(id) !== normalizedAddonId;
                });

                return;
            }

            this.selectedAddonIds.push(normalizedAddonId);
        },

        isAddonSelected(addonId) {
            return this.selectedAddonIds.includes(Number(addonId));
        },

        async submit(showModal = true) {
            if (!this.selectedSaleItemId || this.isSubmitting) {
                return {
                    success: false,
                    message: 'درخواست معتبر نیست.',
                };
            }

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

        openSuccessModal(result) {
            this.modal = {
                type: 'success',
                title: 'با موفقیت به سبد خرید اضافه شد',
                message: result.message ?? 'محصول انتخاب‌شده در سبد خرید شما قرار گرفت.',
                itemsCount: result.cart?.items_count ?? 0,
                subtotalAmount: result.cart?.subtotal_amount ?? 0,
                discountAmount: result.cart?.discount_amount ?? 0,
                totalAmount: result.cart?.total_amount ?? 0,
                selectedAddons: this.selectedAddons,
            };

            this.modalOpen = true;
        },

        openErrorModal(message) {
            this.modal = {
                type: 'error',
                title: 'افزودن به سبد خرید ناموفق بود',
                message,
                itemsCount: 0,
                subtotalAmount: 0,
                discountAmount: 0,
                totalAmount: 0,
                selectedAddons: [],
            };

            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        formatPrice(price) {
            const normalizedPrice = Number(price || 0);

            if (normalizedPrice <= 0) {
                return 'رایگان';
            }

            return new Intl.NumberFormat('fa-IR').format(normalizedPrice) + ' تومان';
        },

        get hasUnsavedChanges() {
            if (!this.inCart) {
                return false;
            }

            const currentAddonIds = [...this.selectedAddonIds].map(Number).sort();
            const originalAddonIds = [...this.originalAddonIds].map(Number).sort();

            return Number(this.selectedSaleItemId) !== Number(this.originalSaleItemId)
                || JSON.stringify(currentAddonIds) !== JSON.stringify(originalAddonIds);
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

                return;
            }

            this.quantity = 1;
            this.canChangeQuantity = false;
            this.maxQuantityPerOrder = null;
            this.originalSaleItemId = null;
            this.originalAddonIds = [];
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
    };
}
