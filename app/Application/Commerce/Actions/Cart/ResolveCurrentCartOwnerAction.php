<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use Illuminate\Support\Facades\Auth;

class ResolveCurrentCartOwnerAction
{
    /**
     * Resolve current cart owner from auth user or session.
     */
    public function execute(): CartOwnerDTO
    {
        return new CartOwnerDTo(
            userId: Auth::id(),
            sessionId: session()->getId(),
        );
    }
}
