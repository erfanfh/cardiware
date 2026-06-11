<?php

namespace App\Livewire\Front\Commerce\Cart;

use App\Application\Commerce\Actions\Cart\AdjustCartItemQuantityAction;
use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Application\Commerce\Actions\Cart\SyncCartItemsAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Commerce\Exceptions\CartItemPriceChangedException;
use App\Application\Financial\Actions\Payment\ResolveDefaultPaymentGatewayAction;
use App\Application\Financial\Actions\Payment\StartPaymentAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Throwable;

class CartPage extends Component
{
    public string $paymentMethod = 'gateway';

    public ?string $couponCode = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $mobile = null;

    public ?string $email = null;

    public ?string $checkoutError = null;

    public ?string $checkoutNotice = null;

    public bool $cartNeedsRefresh = false;

    public function mount(): void
    {
        $user = auth()->user();

        $this->firstName = $user?->first_name;
        $this->lastName = $user?->last_name;
        $this->mobile = $user?->mobile;
        $this->email = $user?->email;
    }

    #[Computed]
    public function cart(): ?Cart
    {
        /** @var Cart|null $cart */
        $cart = app(GetCurrentActiveCartAction::class)->execute();

        if (!$cart) {
            return null;
        }

        $cart->refresh();

        $cart->load([
            'items.children',
            'items.purchasable',
            'items.purchasable.product.picture',
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

    /**
     * @throws Throwable
     */
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

    public function applyCoupon(): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;

        // TODO: Implement coupon logic later.
        // app(ApplyCouponToCartAction::class)->execute($this->cart, $this->couponCode);

        $this->dispatch('coupon-apply-requested');
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

            $this->checkoutNotice = $result['has_changes']
                ? 'سبد خرید شما با آخرین تغییرات به‌روزرسانی شد. لطفا دوباره پرداخت را ادامه دهید.'
                : 'سبد خرید شما از قبل به‌روز بود. می‌توانید پرداخت را ادامه دهید.';

            $this->dispatch('$refresh');
            $this->dispatch('cart-updated');
        } catch (DomainException $exception) {
            $this->cartNeedsRefresh = true;
            $this->checkoutError = 'هنوز امکان به‌روزرسانی کامل سبد خرید وجود ندارد. لطفا آیتم‌های سبد خرید را بررسی کنید.';
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در به‌روزرسانی سبد خرید رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function checkout(): ?RedirectResponse
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;
        $this->paymentMethod = 'gateway';

        if (!auth()->check()) {
            $this->validate([
                'firstName' => ['required', 'string', 'max:255'],
                'lastName' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);
        }

        if (!$this->cart) {
            $this->checkoutError = 'سبد خریدی برای پرداخت یافت نشد.';
            return null;
        }

        try {
            $order = app(CreateOrderFromCartAction::class)->execute(
                cart: $this->cart,
                metadata: [
                    'source' => 'web',
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            );

            $this->cartNeedsRefresh = false;

            $this->resetCartComputedProperties();

            if ($order->total_amount <= 0) {
                // TODO: بعدا CompleteFreeOrderAction را اینجا صدا می‌زنیم.
                $this->checkoutError = 'پردازش سفارش رایگان هنوز پیاده‌سازی نشده است.';
                return null;
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
                    'mobile' => auth()->user()?->mobile ?? $this->mobile,
                ],
            );

            $redirectUrl = data_get($payment->request_payload, 'redirect_url');

            if (!$redirectUrl) {
                $this->checkoutError = 'امکان اتصال به درگاه پرداخت وجود ندارد. لطفا دوباره تلاش کنید.';
                return null;
            }

            return redirect()->away($redirectUrl);
        } catch (CartItemPriceChangedException $exception) {
            $this->checkoutError = $exception->userMessage()
                ?: 'قیمت برخی آیتم‌های سبد خرید تغییر کرده است. لطفا سبد خرید را به‌روزرسانی کنید.';

            $this->checkoutNotice = null;
            $this->cartNeedsRefresh = true;

            $this->resetCartComputedProperties();

            return null;
        } catch (DomainException $exception) {
            $this->checkoutError = 'برخی آیتم‌های سبد خرید تغییر کرده‌اند یا دیگر قابل خرید نیستند. لطفا سبد خرید را به‌روزرسانی کنید و دوباره ادامه دهید.';
            $this->checkoutNotice = null;
            $this->cartNeedsRefresh = true;

            $this->resetCartComputedProperties();

            return null;
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در پردازش پرداخت رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';

            return null;
        }
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

        if (!$purchasable instanceof \App\Application\Commerce\Contracts\Purchasable) {
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
