<?php

namespace App\Livewire\Front\Commerce\Cart;

use App\Application\Commerce\Actions\Cart\AdjustCartItemQuantityAction;
use App\Application\Commerce\Actions\Cart\EnsureCartReadyForCheckoutAction;
use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Application\Commerce\Actions\Cart\SyncCartItemsAction;
use App\Application\Commerce\Actions\Checkout\CompleteFreeOrderAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Commerce\Contracts\Purchasable;
use App\Application\Commerce\Exceptions\AppliedCouponChangedException;
use App\Application\Commerce\Exceptions\CartItemPriceChangedException;
use App\Application\Commerce\Exceptions\CartNeedsRefreshException;
use App\Application\Financial\Actions\Payment\ResolveDefaultPaymentGatewayAction;
use App\Application\Financial\Actions\Payment\StartPaymentAction;
use App\Application\Promotion\Actions\Coupon\ApplyCouponToCartAction;
use App\Application\Promotion\Actions\Coupon\ReleaseCouponReservationForOrderAction;
use App\Application\Promotion\Actions\Coupon\RemoveCouponFromCartAction;
use App\Application\Promotion\Exceptions\CouponMinimumOrderAmountNotMetException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Throwable;

class CartPage extends Component
{
    public string $paymentMethod = 'gateway';

    public ?string $couponCode = null;

    public ?string $checkoutError = null;

    public ?string $checkoutNotice = null;

    public bool $cartNeedsRefresh = false;

    public bool $openCheckoutOnLoad = false;

    public function mount(): void
    {
        $this->openCheckoutOnLoad = (bool)session()->pull('open_checkout_after_auth', false);

        $cart = app(GetCurrentActiveCartAction::class)->execute();

        if ($cart) {
            $this->couponCode = $cart->coupon_code;
        }
    }

    #[Computed]
    public function cart(): ?Cart
    {
        /** @var Cart|null $cart */
        $cart = app(GetCurrentActiveCartAction::class)->execute();

        if (!$cart) {
            return null;
        }

        $cart->loadMissing([
            'items.children',
            'items.purchasable',
            'items.purchasable.product.picture.primaryVariant',
            'items.purchasable.product.picture.variants',
        ]);

        return $cart;
    }

    #[Computed]
    public function cartItems(): Collection
    {
        return $this->cart?->items ?? collect();
    }

    #[Computed]
    public function mainCartItems(): Collection
    {
        return $this->cartItems
            ->filter(fn(CartItem $item): bool => empty($item->parent_cart_item_id))
            ->values();
    }

    #[Computed]
    public function addonItemsByParentId(): Collection
    {
        return $this->cartItems
            ->filter(fn(CartItem $item): bool => !empty($item->parent_cart_item_id))
            ->groupBy('parent_cart_item_id');
    }

    #[Computed]
    public function itemsCount(): int
    {
        return $this->mainCartItems
            ->sum(fn(CartItem $item): int => (int)($item->quantity ?? 1));
    }

    #[Computed]
    public function cartSubtotal(): int
    {
        return (int)(
            $this->cart?->subtotal_amount
            ?? $this->cartItems->sum(fn(CartItem $item): int => $this->itemTotal($item))
        );
    }

    #[Computed]
    public function cartDiscount(): int
    {
        return (int)($this->cart?->discount_amount ?? 0);
    }

    #[Computed]
    public function cartTotal(): int
    {
        return (int)(
            $this->cart?->total_amount
            ?? max(0, $this->cartSubtotal - $this->cartDiscount)
        );
    }

    #[Computed]
    public function walletBalance(): int
    {
        return (int)(auth()->user()?->wallet?->balance ?? 0);
    }

    public function increaseQuantity(
        int                          $cartItemId,
        AdjustCartItemQuantityAction $adjustCartItemQuantityAction
    ): void
    {
        $this->adjustQuantity(
            cartItemId: $cartItemId,
            delta: 1,
            adjustCartItemQuantityAction: $adjustCartItemQuantityAction,
        );
    }

    public function decreaseQuantity(
        int                          $cartItemId,
        AdjustCartItemQuantityAction $adjustCartItemQuantityAction
    ): void
    {
        $this->adjustQuantity(
            cartItemId: $cartItemId,
            delta: -1,
            adjustCartItemQuantityAction: $adjustCartItemQuantityAction,
        );
    }

    private function adjustQuantity(
        int                          $cartItemId,
        int                          $delta,
        AdjustCartItemQuantityAction $adjustCartItemQuantityAction
    ): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;
        $this->cartNeedsRefresh = false;

        try {
            $cartItem = CartItem::query()->findOrFail($cartItemId);

            $adjustCartItemQuantityAction->execute(
                cartItem: $cartItem,
                delta: $delta,
            );

            $this->afterCartMutation();
        } catch (DomainException $exception) {
            $this->checkoutError = $this->cartDomainExceptionMessage($exception);
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در تغییر تعداد آیتم رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function removeItem(int $cartItemId, RemoveCartItemAction $removeCartItemAction): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;
        $this->cartNeedsRefresh = false;

        try {
            $cartItem = CartItem::query()->findOrFail($cartItemId);

            $removeCartItemAction->execute($cartItem);

            $this->dispatch('close-modal', 'remove-cart-item-' . $cartItemId);

            $this->afterCartMutation();
        } catch (DomainException $exception) {
            $this->checkoutError = $this->cartDomainExceptionMessage($exception);
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در حذف آیتم رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function applyCoupon(ApplyCouponToCartAction $applyCouponToCartAction): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;

        if (!$this->cart) {
            $this->checkoutError = 'سبد خریدی برای اعمال کدتخفیف یافت نشد.';
            return;
        }

        try {
            $cart = $applyCouponToCartAction->execute(
                cart: $this->cart,
                code: (string)$this->couponCode,
                user: auth()->user(),
            );

            $this->couponCode = $cart->coupon_code;
            $this->checkoutNotice = 'کدتخفیف با موفقیت اعمال شد.';

            $this->afterCartMutation();
        } catch (DomainException $exception) {
            $this->checkoutError = $this->couponDomainExceptionMessage($exception);
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در اعمال کدتخفیف رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function removeCoupon(RemoveCouponFromCartAction $removeCouponFromCartAction): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;

        if (!$this->cart) {
            $this->checkoutError = 'سبد خریدی برای حذف کدتخفیف یافت نشد.';
            return;
        }

        try {
            $removeCouponFromCartAction->execute($this->cart);

            $this->couponCode = null;
            $this->checkoutNotice = 'کدتخفیف از سبد خرید حذف شد.';

            $this->afterCartMutation();
        } catch (DomainException $exception) {
            $this->checkoutError = $this->couponDomainExceptionMessage($exception);
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در حذف کدتخفیف رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    private function couponDomainExceptionMessage(DomainException $exception): string
    {
        if ($exception instanceof CouponMinimumOrderAmountNotMetException) {
            return 'حداقل مبلغ سبد خرید برای استفاده از این کدتخفیف '
                . $this->money($exception->minimumOrderAmount)
                . ' است.';
        }

        return match ($exception->getMessage()) {
            'Coupon code is required.' => 'لطفاً کدتخفیف را وارد کنید.',
            'Coupon not found.' => 'کدتخفیف واردشده معتبر نیست.',
            'Coupon is not currently usable.' => 'این کدتخفیف در حال حاضر قابل استفاده نیست.',
            'Coupon usage limit reached.' => 'ظرفیت استفاده از این کدتخفیف به پایان رسیده است.',
            'Coupon minimum order amount not met.' => 'مبلغ سبد خرید برای استفاده از این کدتخفیف کافی نیست.',
            'Coupon requires authenticated user.' => 'برای استفاده از این کدتخفیف ابتدا وارد حساب کاربری شوید.',
            'Coupon is not allowed for this user.' => 'این کدتخفیف برای شما قابل استفاده نیست.',
            'Coupon per user usage limit reached.' => 'شما قبلاً از این کدتخفیف به سقف مجاز استفاده کرده‌اید.',
            'Coupon has no eligible cart items.' => 'این کدتخفیف برای آیتم‌های موجود در سبد خرید شما قابل استفاده نیست.',
            'Coupon discount amount is zero.' => 'این کدتخفیف مبلغ قابل کسر برای سبد خرید فعلی ندارد.',
            'Cart is not editable.' => 'سبد خرید در وضعیت قابل ویرایش نیست.',
            'Cart is empty.' => 'سبد خرید شما خالی است.',
            default => 'امکان اعمال این کدتخفیف وجود ندارد.',
        };
    }

    public function refreshCart(SyncCartItemsAction $syncCartItemsAction): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;

        if (!$this->cart) {
            $this->checkoutError = 'سبد خریدی برای به‌روزرسانی یافت نشد.';
            return;
        }

        try {
            $result = $syncCartItemsAction->execute($this->cart);

            $this->cartNeedsRefresh = false;
            $this->checkoutError = null;

            $this->resetCartComputedProperties();

            $this->checkoutNotice = $this->checkoutRefreshMessage($result);

            $this->dispatch('$refresh');
            $this->dispatch('cart-updated');
        } catch (Throwable $exception) {
            report($exception);

            $this->cartNeedsRefresh = true;
            $this->checkoutError = 'خطایی در به‌روزرسانی سبد خرید رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function checkout(
        EnsureCartReadyForCheckoutAction $ensureCartReadyForCheckoutAction,
        CompleteFreeOrderAction          $completeFreeOrderAction,
    ): RedirectResponse|Redirector|null
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;
        $this->paymentMethod = 'gateway';

        if (!auth()->check()) {
            $this->dispatch('open-modal', 'auth-modal');
            return null;
        }

        if (!$this->cart) {
            $this->checkoutError = 'سبد خریدی برای پرداخت یافت نشد.';
            return null;
        }

        try {
            $cart = $ensureCartReadyForCheckoutAction->execute($this->cart);

            $order = app(CreateOrderFromCartAction::class)->execute(
                cart: $cart,
                metadata: [
                    'source' => 'web',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            );

            $this->cartNeedsRefresh = false;

            $this->resetCartComputedProperties();

            if ((int)$order->total_amount <= 0) {
                $payment = $completeFreeOrderAction->execute($order);

                $this->cartNeedsRefresh = false;
                $this->resetCartComputedProperties();

                return redirect()->route('payment.result', [
                    'paymentToken' => $payment->public_token,
                ]);
            }

            $gateway = app(ResolveDefaultPaymentGatewayAction::class)->execute();

            $payment = app(StartPaymentAction::class)->execute(
                payable: $order,
                gateway: $gateway,
                callbackUrl: route('payment.callback', [
                    'gateway' => $gateway->value,
                ]),
                metadata: [
                    'source' => 'web',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'description' => 'پرداخت سفارش ' . $order->order_number,
                    'mobile' => auth()->user()?->mobile,
                ],
            );

            $redirectUrl = data_get($payment->request_payload, 'redirect_url');

            if (!$redirectUrl) {
                app(ReleaseCouponReservationForOrderAction::class)->execute(
                    order: $order,
                    reason: 'payment_redirect_url_missing',
                );

                $this->checkoutError = 'امکان اتصال به درگاه پرداخت وجود ندارد. لطفا دوباره تلاش کنید.';
                return null;
            }

            return redirect()->away($redirectUrl);
        } catch (CartNeedsRefreshException $exception) {
            $this->cartNeedsRefresh = false;
            $this->checkoutError = null;

            $this->resetCartComputedProperties();

            $this->checkoutNotice = $this->checkoutRefreshMessage($exception->syncResult());

            $this->dispatch('$refresh');
            $this->dispatch('cart-updated');

            return null;
        } catch (CartItemPriceChangedException $exception) {
            $this->checkoutError = $exception->userMessage()
                ?: 'قیمت برخی آیتم‌های سبد خرید تغییر کرده است. لطفا دوباره ادامه دهید.';

            $this->checkoutNotice = null;
            $this->cartNeedsRefresh = false;

            $this->resetCartComputedProperties();

            return null;
        } catch (AppliedCouponChangedException $exception) {
            $this->checkoutError = null;

            if ($exception->hasCodeChanged() && $exception->hasAmountChanged()) {
                $this->checkoutNotice =
                    'کدتخفیف اعمال‌شده از «'
                    . $exception->previousCouponCode
                    . '» به «'
                    . $exception->currentCouponCode
                    . '» تغییر کرده و مبلغ سبد خرید شما هم به‌روزرسانی شد. مبلغ نهایی قبلی '
                    . $this->money($exception->previousTotalAmount)
                    . ' بود و مبلغ نهایی جدید '
                    . $this->money($exception->currentTotalAmount)
                    . ' است. لطفاً دوباره ادامه دهید.';
            } elseif ($exception->hasCodeChanged()) {
                $this->checkoutNotice =
                    'کدتخفیف اعمال‌شده از «'
                    . $exception->previousCouponCode
                    . '» به «'
                    . $exception->currentCouponCode
                    . '» تغییر کرده است. لطفاً دوباره ادامه دهید.';
            } else {
                $this->checkoutNotice =
                    'مبلغ سبد خرید شما به دلیل تغییر شرایط کدتخفیف'
                    . ($exception->currentCouponCode ? ' «' . $exception->currentCouponCode . '»' : '')
                    . ' به‌روزرسانی شد. مبلغ نهایی قبلی '
                    . $this->money($exception->previousTotalAmount)
                    . ' بود و مبلغ نهایی جدید '
                    . $this->money($exception->currentTotalAmount)
                    . ' است. لطفاً دوباره ادامه دهید.';
            }

            $this->couponCode = $exception->currentCouponCode;
            $this->cartNeedsRefresh = false;

            $this->resetCartComputedProperties();

            $this->dispatch('$refresh');
            $this->dispatch('cart-updated');

            return null;
        } catch (DomainException $exception) {
            $this->checkoutNotice = null;

            $this->checkoutError = match ($exception->getMessage()) {
                'Cart is empty.' => 'سبد خرید شما خالی شده است. لطفا محصول جدیدی انتخاب کنید.',
                'Cart is not editable.' => 'سبد خرید در وضعیت قابل پرداخت نیست.',
                'Applied coupon is no longer valid.' => 'کدتخفیف اعمال‌شده دیگر معتبر نیست. لطفا سبد خرید را بررسی کرده و دوباره ادامه دهید.',

                'Coupon reservation limit reached.' => 'ظرفیت استفاده از این کدتخفیف به پایان رسیده است.',
                'Coupon per user reservation limit reached.' => 'شما قبلاً از این کدتخفیف استفاده کرده‌اید یا یک سفارش در حال پرداخت با همین کدتخفیف دارید.',
                'Coupon reservation is not reusable.' => 'رزرو قبلی این کدتخفیف دیگر قابل استفاده نیست. لطفاً دوباره تلاش کنید.',

                default => 'امکان ادامه پرداخت وجود ندارد. لطفا سبد خرید را بررسی کنید و دوباره تلاش کنید.',
            };

            $this->cartNeedsRefresh = in_array($exception->getMessage(), [
                'Cart is empty.',
                'Cart is not editable.',
            ], true);

            $this->resetCartComputedProperties();

            return null;
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutNotice = null;
            $this->checkoutError = 'خطایی در پردازش پرداخت رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';

            return null;
        }
    }

    private function checkoutRefreshMessage(array $syncResult): string
    {
        $removedItemsCount = (int)($syncResult['removed_items_count'] ?? 0);
        $updatedItemsCount = (int)($syncResult['updated_items_count'] ?? 0);

        if ($removedItemsCount > 0 && $updatedItemsCount > 0) {
            return 'سبد خرید به‌روزرسانی شد. برخی آیتم‌هایی که دیگر قابل خرید نبودند حذف شدند و قیمت یا اطلاعات برخی آیتم‌ها تغییر کرد. لطفا دوباره ادامه دهید.';
        }

        if ($removedItemsCount > 0) {
            return 'سبد خرید به‌روزرسانی شد. برخی آیتم‌هایی که دیگر قابل خرید نبودند از سبد خرید حذف شدند. لطفا دوباره ادامه دهید.';
        }

        if ($updatedItemsCount > 0) {
            return 'سبد خرید با آخرین قیمت‌ها و اطلاعات محصولات به‌روزرسانی شد. لطفا دوباره ادامه دهید.';
        }

        return 'سبد خرید شما از قبل به‌روز بود. می‌توانید پرداخت را ادامه دهید.';
    }

    public function itemTitle(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return data_get($item->snapshot, 'title')
            ?? data_get($item->snapshot, 'product_title')
            ?? $product?->title
            ?? $purchasable?->title
            ?? $purchasable?->name
            ?? 'آیتم سبد خرید';
    }

    public function itemLink(CartItem $item): string
    {
        $product = $item->purchasable?->product ?? null;

        if ($product instanceof Product) {
            return route('products.detail', $product->slug);
        }

        return '#';
    }

    public function itemDescription(CartItem $item): string
    {
        return $item->purchasable?->product?->excerpt() ?? '';
    }

    public function itemPictureMedia(CartItem $item): mixed
    {
        return $item->purchasable?->product?->picture;
    }

    public function itemImage(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return data_get($item->snapshot, 'image_url')
            ?? $purchasable?->image_url
            ?? $product?->picture?->url
            ?? $product?->image?->url
            ?? $product?->media?->first()?->url
            ?? asset('front/assets/images/placeholder.webp');
    }

    public function itemBadge(CartItem $item): string
    {
        $type = data_get($item->snapshot, 'type');

        if ($type === 'product_addon') {
            return '+ خدمت افزوده';
        }

        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        $productType = $product?->type?->value ?? $product?->type ?? null;

        return $productType === 'DIGITAL'
            ? 'فایل دیجیتال'
            : 'محصول';
    }

    public function itemMaxQuantity(CartItem $item): int
    {
        $purchasable = $item->purchasable;

        if (!$purchasable instanceof Purchasable) {
            return 1;
        }

        return (int)($purchasable->getMaxQuantityPerOrder() ?? 1);
    }

    public function itemTotal(CartItem $item): int
    {
        return (int)(
            $item->total_amount
            ?? ((int)($item->unit_price ?? 0) * (int)($item->quantity ?? 1))
        );
    }

    public function money(int $amount): string
    {
        return number_format($amount) . ' تومان';
    }

    private function afterCartMutation(): void
    {
        $this->resetCartComputedProperties();

        $this->dispatch('$refresh');
        $this->dispatch('cart-updated');
    }

    private function cartDomainExceptionMessage(DomainException $exception): string
    {
        return match ($exception->getMessage()) {
            'Cart is not editable.' => 'سبد خرید در وضعیت قابل ویرایش نیست.',
            'Quantity must be at least 1.' => 'تعداد انتخاب‌شده معتبر نیست.',
            'Dependent cart items cannot have quantity greater than 1.' => 'تعداد خدمات افزوده به‌صورت جداگانه قابل تغییر نیست.',
            'Cart item has invalid purchasable model.' => 'آیتم انتخاب‌شده معتبر نیست.',
            'Requested quantity exceeds the allowed maximum per order.' => 'تعداد انتخاب‌شده بیشتر از سقف مجاز خرید این آیتم است.',
            default => 'امکان انجام این عملیات روی سبد خرید وجود ندارد.',
        };
    }

    private function resetCartComputedProperties(): void
    {
        unset($this->cart);
        unset($this->cartItems);
        unset($this->mainCartItems);
        unset($this->addonItemsByParentId);
        unset($this->itemsCount);
        unset($this->cartSubtotal);
        unset($this->cartDiscount);
        unset($this->cartTotal);
        unset($this->walletBalance);
    }

    public function render(): View
    {
        return view('livewire.front.commerce.cart.cart-page');
    }
}
