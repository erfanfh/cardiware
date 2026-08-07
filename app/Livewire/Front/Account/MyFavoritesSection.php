<?php

namespace App\Livewire\Front\Account;

use App\Application\Identity\Actions\Favorites\GetUserFavoriteProductsAction;
use App\Application\Identity\Actions\Favorites\RemoveProductFavoriteAction;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator as EmptyPaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class MyFavoritesSection extends Component
{
    use WithPagination;

    public int $perPage = 9;

    public ?string $message = null;

    public int $messageVersion = 0;

    #[Computed]
    public function favorites(): LengthAwarePaginator
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            return new EmptyPaginator([], 0, $this->perPage);
        }

        return app(GetUserFavoriteProductsAction::class)->execute(
            user: $user,
            perPage: $this->perPage,
        );
    }

    public function remove(
        int $productId,
        RemoveProductFavoriteAction $removeProductFavoriteAction,
    ): void
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            return;
        }

        $removed = $removeProductFavoriteAction->execute($user, $productId);

        if (!$removed) {
            return;
        }

        unset($this->favorites);

        $this->message = 'محصول از لیست علاقه مندی های شما حذف شد.';
        $this->messageVersion++;

        if ($this->favorites->isEmpty() && $this->favorites->currentPage() > 1) {
            $this->previousPage();
        }
    }

    public function render(): View
    {
        return view('livewire.front.account.my-favorites-section');
    }
}
