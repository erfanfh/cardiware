<?php

namespace App\Domain\Support\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number',
        'user_id',
        'department_id',
        'product_id',
        'title',
        'status',
        'is_under_review',
        'under_review_marked_at',
        'assigned_to_user_id',
        'last_message_at',
        'last_user_message_at',
        'last_support_message_at',
        'closed_at',
        'closed_by_user_id',
    ];

    protected $casts = [
        'status' => TicketStatusEnum::class,
        'is_under_review' => 'boolean',
        'under_review_marked_at' => 'datetime',
        'last_message_at' => 'datetime',
        'last_user_message_at' => 'datetime',
        'last_support_message_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(TicketMessage::class)
            ->latestOfMany();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TicketDepartment::class, 'department_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', TicketStatusEnum::CLOSED->value);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', TicketStatusEnum::CLOSED->value);
    }

    /**
     * تیکت‌های علامت‌خورده به‌عنوان «در حال بررسی» توسط پشتیبان.
     */
    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('is_under_review', true);
    }

    /**
     * اولویت نمایش در لیست پشتیبانی:
     * ۱) تیکت‌های کاربران با اشتراک VIP فعال بالاتر از بقیه
     * ۲) تیکت‌های محصول رایگان در اولویت آخر
     * وضعیت VIP به‌صورت زنده از جدول اشتراک‌ها بررسی می‌شود؛ پس با پایان
     * اشتراک، اولویت تیکت خودکار به حالت عادی برمی‌گردد.
     */
    public function scopeOrderBySupportPriority(Builder $query): Builder
    {
        $now = now();

        return $query
            ->orderByRaw(
                "CASE WHEN EXISTS (
                    SELECT 1 FROM vip_subscriptions vs
                    WHERE vs.user_id = tickets.user_id
                      AND vs.revoked_at IS NULL
                      AND vs.starts_at <= ?
                      AND vs.expires_at > ?
                ) THEN 0 ELSE 1 END",
                [$now, $now],
            )
            ->orderByRaw(
                "CASE WHEN tickets.product_id IS NOT NULL AND COALESCE((
                    SELECT MIN(psi.base_price) FROM product_sale_items psi
                    WHERE psi.product_id = tickets.product_id
                      AND psi.deleted_at IS NULL
                ), 1) = 0 THEN 1 ELSE 0 END",
            );
    }

    public function scopeLatestActivity(Builder $query): Builder
    {
        return $query->orderByDesc('last_message_at')
            ->orderByDesc('id');
    }

    public function isClosed(): bool
    {
        return $this->status === TicketStatusEnum::CLOSED;
    }

    public function isOpen(): bool
    {
        return !$this->isClosed();
    }

    public function isUnderReview(): bool
    {
        return (bool) $this->is_under_review;
    }

    /**
     * تیکتِ محصولی که گزینه رایگان دارد (ارزان‌ین نسخه = رایگان) — اولویت آخر پشتیبانی.
     */
    public function isFreeProductTicket(): bool
    {
        if ($this->product_id === null || !$this->product) {
            return false;
        }

        $saleItems = $this->product->relationLoaded('saleItems')
            ? $this->product->saleItems
            : $this->product->saleItems()->get();

        $minPrice = $saleItems->min('base_price');

        return $minPrice !== null && (int) $minPrice === 0;
    }

    public function canReceiveReply(): bool
    {
        return $this->isOpen();
    }
}
