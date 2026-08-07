<?php

namespace App\Livewire\Front\Catalog;

use App\Application\Identity\Actions\Favorites\IsProductFavoriteAction;
use App\Application\Identity\Actions\Favorites\ToggleProductFavoriteAction;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ProductFavoriteButton extends Component
{
    public Product $product;

    public bool $isFavorite = false;

    public function mount(
        Product $product,
        IsProductFavoriteAction $isProductFavoriteAction,
    ): void
    {
        $this->product = $product;

        $user = auth()->user();

        $this->isFavorite = $user instanceof User
            && $isProductFavoriteAction->execute($user, $product);
    }

    public function toggle(
        ToggleProductFavoriteAction $toggleProductFavoriteAction,
    ): void
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            $this->showMessage(
                message: 'برای افزودن محصول به علاقه مندی ها، ابتدا وارد حساب کاربری شوید.',
                type: 'warning',
            );

            return;
        }

        $this->isFavorite = $toggleProductFavoriteAction->execute(
            user: $user,
            product: $this->product,
        );

        $this->showMessage(
            message: $this->isFavorite
                ? 'محصول به علاقه مندی های شما اضافه شد.'
                : 'محصول از علاقه مندی های شما حذف شد.',
            type: $this->isFavorite ? 'success' : 'info',
        );
    }

    public function render(): View
    {
        return view('livewire.front.catalog.product-favorite-button');
    }

    private function showMessage(string $message, string $type): void
    {
        $this->dispatch(
            'product-favorite-toast',
            message: $message,
            type: $type,
        );
    }
}
