<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'expires_at',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'synced_at',
    ];

    protected $casts = [
        'status' => CartStatusEnum::class,
        'expires_at' => 'datetime',
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'total_amount' => 'integer',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all cart items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get root cart items.
     */
    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_cart_item_id');
    }

    /**
     * Get child cart items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_cart_item_id')->orderBy('id');
    }

    /**
     * Scope active carts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CartStatusEnum::ACTIVE->value);
    }

    /**
     * Determine whether the cart is editable.
     */
    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Determine whether cart belongs to a guest session.
     */
    public function isGuestCart(): bool
    {
        return $this->user_id === null && filled($this->session_id);
    }
}
