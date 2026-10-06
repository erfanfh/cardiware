<?php

namespace App\Livewire\Front\Catalog;

use App\Application\Catalog\Actions\GetProductBuyBoxDataAction;
use App\Application\Commerce\Actions\Cart\AddProductSaleItemWithAddonsToCartAction;
use App\Application\Commerce\Actions\Cart\GetProductCartStateAction;
use App\Application\Commerce\Actions\Cart\RemoveProductFromCartAction;
use App\Application\Vip\Actions\ClaimVipSaleItemAction;
use App\Domain\Catalog\Models\Product;
use DomainException;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Throwable;

class ProductBuyBox extends Component
{
    public Product $product;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $saleItems = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $addons = [];

    public array $purchasePoints = [];

    /**
     * @var array<string, mixed>
     */
    public array $purchaseState = [];

    public array $actionLinks = [];

    public bool $hasAvailableSaleItems = false;

    public bool $hasActiveVip = false;

    /** @var array<int, int> */
    public array $claimedVipSaleItemIds = [];

    /** @var array<int, int> */
    public array $ownedSaleItemIds = [];

    public function mount(
        Product                    $product,
        GetProductBuyBoxDataAction $getProductBuyBoxDataAction,
        GetProductCartStateAction  $getProductCartStateAction,
    ): void
    {
        $this->product = $product;

        $buyBoxData = $getProductBuyBoxDataAction->execute($product);

        $this->saleItems = $buyBoxData['saleItems'];
        $this->addons = $buyBoxData['addons'];
        $this->purchasePoints = $buyBoxData['purchasePoints'] ?? [];
        $this->hasAvailableSaleItems = $buyBoxData['hasAvailableSaleItems'];
        $this->hasActiveVip = (bool) auth()->user()?->hasActiveVipSubscription();
        $this->claimedVipSaleItemIds = auth()->user()?->vipSaleItemClaims()
            ->whereNotNull('product_sale_item_id')
            ->whereHas('saleItem', fn ($query) => $query->where('product_id', $product->getKey()))
            ->pluck('product_sale_item_id')->map(fn ($id) => (int) $id)->all() ?? [];
        $this->ownedSaleItemIds = auth()->user()?->purchaseAccesses()
            ->usable()
            ->where('product_id', $product->getKey())
            ->pluck('product_sale_item_id')->map(fn ($id) => (int) $id)->all() ?? [];

        $this->purchaseState = $getProductCartStateAction->execute($product);

        $this->actionLinks = $product->actionLinks()
            ->get([
                'id',
                'title',
                'url',
                'open_in_new_tab',
            ])
            ->map(fn($link) => [
                'id' => $link->id,
                'title' => $link->title,
                'url' => $link->url,
                'open_in_new_tab' => $link->open_in_new_tab,
            ])
            ->values()
            ->all();
    }

    public function claimVipSaleItem(int $saleItemId, ClaimVipSaleItemAction $action): void
    {
        abort_unless(auth()->check(), 403);

        $saleItem = $this->product->saleItems()->findOrFail($saleItemId);
        $action->execute(auth()->user(), $saleItem);

        $this->redirectRoute('account.downloads', ['vipItem' => $saleItemId]);
    }

    /**
     * Add or update selected product sale item with selected addons.
     *
     * @param array<int, int|string> $addonIds
     * @return array<string, mixed>
     */
    #[Renderless]
    public function addToCart(
        AddProductSaleItemWithAddonsToCartAction $action,
        int                                      $saleItemId,
        array                                    $addonIds = [],
        int                                      $quantity = 1,
    ): array
    {
        if (!$this->hasAvailableSaleItems) {
            return [
                'success' => false,
                'message' => 'این محصول در حال حاضر ناموجود است.',
            ];
        }

        try {
            $cart = $action->execute(
                productSaleItemId: $saleItemId,
                addonIds: $addonIds,
                quantity: $quantity,
            );

            $state = $this->refreshPurchaseState();

            $summary = [
                'id' => $cart->id,
                'items_count' => $cart->items()
                    ->whereNull('parent_cart_item_id')
                    ->count(),
                'subtotal_amount' => (int)$cart->subtotal_amount,
                'discount_amount' => (int)$cart->discount_amount,
                'taxable_amount' => (int)$cart->taxable_amount,
                'total_amount' => (int)$cart->total_amount,
            ];

            $this->dispatch(
                'cart-updated',
                source: 'product-buy-box',
                productId: $this->product->id,
                cart: $summary,
            );

            return [
                'success' => true,
                'message' => 'سبد خرید با موفقیت به‌روزرسانی شد.',
                'state' => $state,
                'cart' => $summary,
            ];
        } catch (DomainException $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'خطایی هنگام به‌روزرسانی سبد خرید رخ داد.',
            ];
        }
    }

    /**
     * Remove current product from cart.
     *
     * @return array<string, mixed>
     */
    #[Renderless]
    public function removeFromCart(
        RemoveProductFromCartAction $removeProductFromCartAction,
    ): array
    {
        try {
            $removeProductFromCartAction->execute($this->product);

            $state = $this->refreshPurchaseState();

            $this->dispatch(
                'cart-updated',
                source: 'product-buy-box',
                productId: $this->product->id,
            );

            return [
                'success' => true,
                'message' => 'محصول از سبد خرید حذف شد.',
                'state' => $state,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => 'خطایی هنگام حذف محصول از سبد خرید رخ داد.',
            ];
        }
    }

    /**
     * Refresh this product buy box when cart is changed from another component,
     * for example header cart dropdown.
     */
    #[On('cart-updated')]
    #[Renderless]
    public function refreshPurchaseStateFromCart(?string $source = null, ?int $productId = null): void
    {
        if ($source === 'product-buy-box') {
            return;
        }

        $state = $this->refreshPurchaseState();

        $this->dispatch(
            'product-buy-box-state-refreshed',
            productId: $this->product->id,
            state: $state,
        );
    }

    public function render(): View
    {
        return view('livewire.front.catalog.product-buy-box');
    }

    /**
     * @return array<string, mixed>
     */
    private function refreshPurchaseState(): array
    {
        $this->purchaseState = app(GetProductCartStateAction::class)
            ->execute($this->product);

        return $this->purchaseState;
    }
}
