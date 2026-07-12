<?php

namespace App\Livewire\Front\Account\Wallet;

use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsList extends Component
{
    use WithPagination;

    /**
     * The wallet ID is kept as component state, but every query
     * also verifies that it belongs to the authenticated user.
     */
    public int $walletId;

    public string $direction = '';

    public string $type = '';

    public function mount(
        GetOrCreateWalletAction $getOrCreateWalletAction,
    ): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $wallet = $getOrCreateWalletAction->execute($user);

        $this->walletId = (int)$wallet->id;
    }

    public function updatedDirection(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'direction',
            'type',
        ]);

        $this->resetPage();
    }

    public function render(): View
    {
        /*
         * Do not trust walletId received from the browser.
         * It must always belong to the authenticated user.
         */
        $wallet = Wallet::query()
            ->whereKey($this->walletId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $transactionsQuery = $wallet->transactions()
            ->with('reference')
            ->latest('id');

        $direction = WalletTransactionDirectionEnum::tryFrom(
            $this->direction,
        );

        if ($direction) {
            $transactionsQuery->where(
                'direction',
                $direction->value,
            );
        }

        $type = WalletTransactionTypeEnum::tryFrom($this->type);

        if ($type) {
            $transactionsQuery->where(
                'type',
                $type->value,
            );
        }

        return view(
            'livewire.front.account.wallet.transactions-list',
            [
                'transactions' => $transactionsQuery->paginate(10),
                'directionOptions' => WalletTransactionDirectionEnum::cases(),
                'typeOptions' => WalletTransactionTypeEnum::cases(),
            ],
        );
    }
}
