<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Promotion\Models\Coupon;
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
        'taxable_amount',
        'tax_rate_basis_points',
        'tax_amount',
        'total_amount',
        'synced_at',
        'coupon_id',
        'coupon_code',
        'coupon_eligible_amount',
        'coupon_discount_amount',
        'coupon_applied_at',
    ];

    protected $casts = [
        'status' => CartStatusEnum::class,
        'expires_at' => 'datetime',
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'taxable_amount' => 'integer',
        'tax_rate_basis_points' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'synced_at' => 'datetime',
        'coupon_eligible_amount' => 'integer',
        'coupon_discount_amount' => 'integer',
        'coupon_applied_at' => 'datetime',
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

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
