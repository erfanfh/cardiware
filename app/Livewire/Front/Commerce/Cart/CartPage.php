<?php

namespace App\Livewire\Front\Commerce\Cart;

use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Application\Commerce\Actions\Cart\SyncCartItemsAction;
use App\Application\Commerce\Actions\Order\CreateOrderFromCartAction;
use App\Application\Commerce\Exceptions\CartItemPriceChangedException;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Contracts\View\View;
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

        $cart?->load([
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
    public function cartTax(): int
    {
        return 0;
    }

    #[Computed]
    public function cartTotal(): int
    {
        return (int)(
            $this->cart?->total_amount
            ?? max(0, $this->cartSubtotal - $this->cartDiscount + $this->cartTax)
        );
    }

    #[Computed]
    public function walletBalance(): int
    {
        return (int)(auth()->user()?->wallet?->balance ?? 0);
    }

    /**
     * @throws Throwable
     */
    public function removeItem(int $cartItemId, RemoveCartItemAction $removeCartItemAction): void
    {
        $this->checkoutError = null;
        $this->checkoutNotice = null;
        $this->cartNeedsRefresh = false;

        $cartItem = CartItem::query()->findOrFail($cartItemId);

        $removeCartItemAction->execute($cartItem);

        $this->resetCartComputedProperties();

        $this->dispatch('cart-updated');
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

            $this->resetCartComputedProperties();

            $this->checkoutNotice = $result['has_changes']
                ? 'سبد خرید شما با آخرین قیمت‌ها به‌روزرسانی شد.'
                : 'سبد خرید شما از قبل به‌روز بود.';

            $this->dispatch('cart-updated');
        } catch (DomainException $exception) {
            $this->checkoutError = 'امکان به‌روزرسانی سبد خرید وجود ندارد. لطفا سبد خرید خود را بررسی کنید.';
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در به‌روزرسانی سبد خرید رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
        }
    }

    public function checkout(): void
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
            return;
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

            // مرحله بعد:
            // app(StartPaymentAction::class)->execute($order);

            $this->dispatch('checkout-requested');
        } catch (CartItemPriceChangedException $exception) {
            $this->checkoutError = $exception->userMessage();
            $this->cartNeedsRefresh = true;

            $this->resetCartComputedProperties();

            return;
        } catch (DomainException $exception) {
            $this->checkoutError = 'امکان تکمیل پرداخت وجود ندارد. لطفا سبد خرید خود را بررسی کنید.';

            return;
        } catch (Throwable $exception) {
            report($exception);

            $this->checkoutError = 'خطایی در پردازش پرداخت رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';

            return;
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

    public function itemDescription(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return data_get($item->snapshot, 'description')
            ?? $purchasable?->description
            ?? $product?->short_description
            ?? $product?->description
            ?? 'این آیتم در سبد خرید شما قرار دارد.';
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
            return 'خدمت افزوده';
        }

        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        $productType = $product?->type?->value ?? $product?->type ?? null;

        return $productType === 'DIGITAL'
            ? 'فایل دیجیتال'
            : 'محصول';
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

    private function resetCartComputedProperties(): void
    {
        unset($this->cart);
        unset($this->cartItems);
        unset($this->mainCartItems);
        unset($this->addonItemsByParentId);
        unset($this->itemsCount);
        unset($this->cartSubtotal);
        unset($this->cartDiscount);
        unset($this->cartTax);
        unset($this->cartTotal);
        unset($this->walletBalance);
    }

    public function render(): View
    {
        return view('livewire.front.commerce.cart.cart-page');
    }
}
