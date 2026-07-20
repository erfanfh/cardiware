<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Catalog\Actions\BuildPurchaseAccessManifestAction;
use App\Application\Commerce\Exceptions\PurchaseAccessIsNotUsableException;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Identity\Models\User;

class GetUserLibraryManifestAction
{
    public function __construct(
        private readonly BuildPurchaseAccessManifestAction $buildManifestAction,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, int $purchaseAccessId): array
    {
        $purchaseAccess = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccessId)
            ->where('user_id', $user->getKey())
            ->firstOrFail();

        if (!$purchaseAccess->isUsable()) {
            throw new PurchaseAccessIsNotUsableException();
        }

        return $this->buildManifestAction->execute($purchaseAccess);
    }
}
