<?php

namespace App\Livewire\Front\Commerce\Cart;

use App\Application\Commerce\Actions\Cart\GetActiveCartItemsCountAction;
use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Commerce\Actions\Cart\RemoveCartItemAction;
use App\Application\Commerce\Actions\Cart\SyncCartIfStaleAction;
use App\Application\Commerce\Actions\Cart\UpdateCartItemQuantityAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Livewire\Front\Catalog\ProductBuyBox;
use DomainException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class HeaderCartDropdown extends Component
{
    public array $summary = [
        'items_count' => 0,
        'subtotal_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 0,
        'items' => [],
    ];

    public bool $hasLoadedOnce = false;

    public function mount(): void
    {
        $this->loadCountOnly();
    }

    public function openDropdown(): void
    {
        $this->reloadCart(syncIfStale: true);

        $this->hasLoadedOnce = true;
    }

    #[On('cart-updated')]
    public function handleExternalCartUpdated(?string $source = null): void
    {
        if ($source === 'header-cart') {
            return;
        }

        if ($this->hasLoadedOnce) {
            $this->reloadCart(syncIfStale: true);
        } else {
            $this->loadCountOnly();
        }
    }

    public function reloadCart(bool $syncIfStale = true): void
    {
        $cart = $this->currentCart();

        if (!$cart) {
            $this->resetSummary();

            return;
        }

        if ($syncIfStale) {
            app(SyncCartIfStaleAction::class)->execute($cart);
        }

        $cart = $cart->fresh([
            'rootItems.children.purchasable',
            'rootItems.purchasable',
        ]);

        $this->summary = [
            'items_count' => (int)$cart->rootItems->sum('quantity'),
            'subtotal_amount' => (int)$cart->subtotal_amount,
            'discount_amount' => (int)$cart->discount_amount,
            'total_amount' => (int)$cart->total_amount,
            'items' => $cart->rootItems
                ->sortBy('id')
                ->map(fn(CartItem $item) => $this->serializeItem($item))
                ->values()
                ->all(),
        ];
    }

    public function increase(int $cartItemId): void
    {
        $item = $this->findEditableCartItem($cartItemId);

        if (!$item) {
            $this->reloadCart();

            return;
        }

        $maxQuantity = $this->maxQuantityForItem($item);

        if ($maxQuantity !== null && $item->quantity >= $maxQuantity) {
            $this->reloadCart();

            $this->dispatch(
                'cart-quantity-limit-reached',
                message: 'امکان افزایش تعداد این آیتم بیشتر از حد مجاز وجود ندارد.',
            );

            return;
        }

        try {
            app(UpdateCartItemQuantityAction::class)->execute($item, $item->quantity + 1);

            $this->reloadCart();
            $this->notifyProductBuyBoxes();
        } catch (DomainException $exception) {
            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: $exception->getMessage(),
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: 'خطایی هنگام به‌روزرسانی سبد خرید رخ داد.',
            );
        }
    }

    public function decrease(int $cartItemId): void
    {
        $item = $this->findEditableCartItem($cartItemId);

        if (!$item) {
            $this->reloadCart();

            return;
        }

        if ($item->quantity <= 1) {
            $this->remove($cartItemId);

            return;
        }

        try {
            app(UpdateCartItemQuantityAction::class)->execute($item, $item->quantity - 1);

            $this->reloadCart();
            $this->notifyProductBuyBoxes();
        } catch (DomainException $exception) {
            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: $exception->getMessage(),
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: 'خطایی هنگام به‌روزرسانی سبد خرید رخ داد.',
            );
        }
    }

    public function remove(int $cartItemId): void
    {
        $item = $this->findEditableCartItem($cartItemId);

        if (!$item) {
            $this->reloadCart();

            return;
        }

        try {
            app(RemoveCartItemAction::class)->execute($item);

            $this->reloadCart();
            $this->notifyProductBuyBoxes();
        } catch (DomainException $exception) {
            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: $exception->getMessage(),
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->reloadCart();

            $this->dispatch(
                'cart-operation-failed',
                message: 'خطایی هنگام حذف آیتم از سبد خرید رخ داد.',
            );
        }
    }

    public function getCartUrlProperty(): string
    {
        return Route::has('cart.index')
            ? route('cart.index')
            : '#';
    }

    public function getCheckoutUrlProperty(): string
    {
        return Route::has('checkout.index')
            ? route('checkout.index')
            : $this->cartUrl;
    }

    public function render(): View
    {
        return view('livewire.front.commerce.cart.header-cart-dropdown');
    }

    private function currentCart(): ?Cart
    {
        return app(GetCurrentActiveCartAction::class)->execute();
    }

    private function findEditableCartItem(int $cartItemId): ?CartItem
    {
        $cart = $this->currentCart();

        if (!$cart || !$cart->isEditable()) {
            return null;
        }

        return $cart
            ->items()
            ->with('purchasable')
            ->whereKey($cartItemId)
            ->whereNull('parent_cart_item_id')
            ->first();
    }

    private function loadCountOnly(): void
    {
        $this->summary['items_count'] = app(GetActiveCartItemsCountAction::class)->execute();
    }

    private function serializeItem(CartItem $item): array
    {
        $maxQuantity = $this->maxQuantityForItem($item);

        $pricing = Arr::get($item->snapshot ?? [], 'pricing', []);

        $quantity = max(1, (int)$item->quantity);

        $regularUnitPrice = (int)Arr::get($pricing, 'base_price', $item->unit_price);
        $finalUnitPrice = (int)Arr::get($pricing, 'final_price', $item->unit_price);
        $unitDiscountAmount = (int)Arr::get($pricing, 'unit_discount_amount', max(0, $regularUnitPrice - $finalUnitPrice));

        $hasDiscount = (bool)Arr::get($pricing, 'has_discount', $unitDiscountAmount > 0)
            && $regularUnitPrice > $finalUnitPrice;

        $regularTotalAmount = $hasDiscount
            ? $regularUnitPrice * $quantity
            : (int)$item->total_amount;

        return [
            'id' => $item->id,
            'title' => $this->itemTitle($item),
            'subtitle' => $this->itemSubtitle($item),
            'url' => $item->url,
            'image_url' => $this->itemImageUrl($item),
            'quantity' => $quantity,
            'unit_price' => (int)$item->unit_price,
            'total_amount' => (int)$item->total_amount,

            'has_discount' => $hasDiscount,
            'regular_unit_price' => $regularUnitPrice,
            'regular_total_amount' => $regularTotalAmount,
            'unit_discount_amount' => $unitDiscountAmount,

            'max_quantity' => $maxQuantity,
            'can_decrease' => $item->quantity > 1,
            'can_increase' => $maxQuantity === null || $item->quantity < $maxQuantity,
            'children' => $item->children
                ->sortBy('id')
                ->map(fn(CartItem $child) => [
                    'id' => $child->id,
                    'title' => $this->itemTitle($child),
                    'quantity' => (int)$child->quantity,
                    'total_amount' => (int)$child->total_amount,
                ])
                ->values()
                ->all(),
        ];
    }

    private function maxQuantityForItem(CartItem $item): ?int
    {
        $purchasable = $item->purchasable;

        if ($purchasable && method_exists($purchasable, 'getMaxQuantityPerOrder')) {
            $maxQuantity = $purchasable->getMaxQuantityPerOrder();

            return filled($maxQuantity) ? max(1, (int)$maxQuantity) : null;
        }

        $snapshotMaxQuantity = Arr::get($item->snapshot ?? [], 'max_quantity_per_order');

        return filled($snapshotMaxQuantity) ? max(1, (int)$snapshotMaxQuantity) : null;
    }

    private function itemTitle(CartItem $item): string
    {
        $snapshot = $item->snapshot ?? [];

        return Arr::get($snapshot, 'title')
            ?? Arr::get($snapshot, 'product_title')
            ?? Arr::get($snapshot, 'product.title')
            ?? Arr::get($snapshot, 'name')
            ?? $item->purchasable?->getPurchasableTitle()
            ?? $item->purchasable?->title
            ?? 'آیتم سبد خرید';
    }

    private function itemSubtitle(CartItem $item): ?string
    {
        $snapshot = $item->snapshot ?? [];

        $parts = array_filter([
            Arr::get($snapshot, 'sku'),
            Arr::get($snapshot, 'product_sale_item_title'),
            Arr::get($snapshot, 'sale_item_title'),
            Arr::get($snapshot, 'variation_title'),
            Arr::get($snapshot, 'attributes_label'),
        ]);

        return filled($parts) ? implode(' • ', $parts) : null;
    }

    private function itemImageUrl(CartItem $item): ?string
    {
        $snapshot = $item->snapshot ?? [];

        return Arr::get($snapshot, 'image_url')
            ?? Arr::get($snapshot, 'picture_url')
            ?? Arr::get($snapshot, 'thumbnail_url')
            ?? Arr::get($snapshot, 'product.image_url')
            ?? Arr::get($snapshot, 'product.picture_url');
    }

    private function notifyProductBuyBoxes(): void
    {
        $this->dispatch('cart-updated', source: 'header-cart')
            ->to(ProductBuyBox::class);
    }

    private function resetSummary(): void
    {
        $this->summary = [
            'items_count' => 0,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'items' => [],
        ];
    }
}
