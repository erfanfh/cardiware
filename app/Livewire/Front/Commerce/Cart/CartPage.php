<?php

namespace App\Livewire\Front\Commerce\Cart;

use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
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
            $this->cart?->subtotal
            ?? $this->cart?->subtotal_amount
            ?? $this->cart?->items_subtotal
            ?? $this->cartItems->sum(fn(CartItem $item): int => $this->itemTotal($item))
        );
    }

    #[Computed]
    public function cartDiscount(): int
    {
        return (int)(
            $this->cart?->discount_total
            ?? $this->cart?->discount_amount
            ?? $this->cart?->total_discount
            ?? 0
        );
    }

    #[Computed]
    public function cartTax(): int
    {
        return (int)(
            $this->cart?->tax_total
            ?? $this->cart?->tax_amount
            ?? $this->cart?->total_tax
            ?? 0
        );
    }

    #[Computed]
    public function cartTotal(): int
    {
        return (int)(
            $this->cart?->total
            ?? $this->cart?->total_amount
            ?? $this->cart?->payable_amount
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
        $cartItem = CartItem::query()->findOrFail($cartItemId);

        $removeCartItemAction->execute($cartItem);

        $this->resetCartComputedProperties();

        $this->dispatch('cart-updated');
    }

    public function applyCoupon(): void
    {
        // TODO: Implement coupon logic later.
        // Example:
        // app(ApplyCouponToCartAction::class)->execute($this->cart, $this->couponCode);

        $this->dispatch('coupon-apply-requested');
    }

    public function checkout(): void
    {
        $this->paymentMethod = 'gateway';

        if (!auth()->check()) {
            $this->validate([
                'firstName' => ['required', 'string', 'max:255'],
                'lastName' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:255'],
            ]);
        }

        // TODO: Implement checkout logic later.
        // Example:
        // app(StartCheckoutAction::class)->execute(
        //     cart: $this->cart,
        //     paymentMethod: $this->paymentMethod,
        // );

        $this->dispatch('checkout-requested');
    }

    public function itemTitle(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return $item->title
            ?? $product?->title
            ?? $purchasable?->title
            ?? $purchasable?->name
            ?? 'آیتم سبد خرید';
    }

    public function itemDescription(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return $item->description
            ?? $purchasable?->description
            ?? $product?->short_description
            ?? $product?->description
            ?? 'این آیتم در سبد خرید شما قرار دارد.';
    }

    public function itemImage(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        return $item->image_url
            ?? $purchasable?->image_url
            ?? $product?->picture?->url
            ?? $product?->image?->url
            ?? $product?->media?->first()?->url
            ?? asset('front/assets/images/placeholder.webp');
    }

    public function itemBadge(CartItem $item): string
    {
        $purchasable = $item->purchasable;
        $product = $purchasable?->product ?? null;

        $type = $product?->type?->value ?? $product?->type ?? null;

        return $type === 'DIGITAL'
            ? 'فایل دیجیتال'
            : 'محصول';
    }

    public function itemTotal(CartItem $item): int
    {
        return (int)(
            $item->total
            ?? $item->line_total
            ?? $item->subtotal
            ?? $item->line_subtotal
            ?? ((int)($item->price ?? $item->unit_price ?? 0) * (int)($item->quantity ?? 1))
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
