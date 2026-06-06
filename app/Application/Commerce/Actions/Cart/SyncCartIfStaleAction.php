<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;

class SyncCartIfStaleAction
{
    public function __construct(
        private readonly SyncCartItemsAction $syncCartItemsAction,
    )
    {
    }

    /**
     * Sync cart only if it is stale.
     *
     * @return array<string, mixed>
     */
    public function execute(Cart $cart, int $ttlMinutes = 5): array
    {
        if (
            $cart->synced_at !== null
            && $cart->synced_at->greaterThan(now()->subMinutes($ttlMinutes))
        ) {
            return [
                'cart' => $cart,
                'changed_items' => [],
                'has_changes' => false,
                'skipped' => true,
            ];
        }

        return $this->syncCartItemsAction->execute($cart);
    }
}
