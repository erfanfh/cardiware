<?php

namespace App\Application\Commerce\DTOs;

use App\Domain\Commerce\Models\Cart;
use DomainException;

final readonly class CartOwnerDTO
{
    public function __construct(
        public ?int    $userId,
        public ?string $sessionId,
    )
    {
        if ($this->userId === null && blank($this->sessionId)) {
            throw new DomainException('Cart owner must have either user id or session id.');
        }
    }

    /**
     * Determine whether the owner is authenticated user.
     */
    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }

    /**
     * Determine whether the owner is guest.
     */
    public function isGuest(): bool
    {
        return $this->userId === null;
    }

    /**
     * Check if the given cart belongs to this owner.
     */
    public function owns(Cart $cart): bool
    {
        if ($this->userId !== null) {
            return (int)$cart->user_id === (int)$this->userId;
        }

        return $cart->user_id === null
            && filled($cart->session_id)
            && hash_equals((string)$cart->session_id, (string)$this->sessionId);
    }
}
