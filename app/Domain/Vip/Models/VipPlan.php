<?php

namespace App\Domain\Vip\Models;

use App\Application\Commerce\Contracts\Purchasable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VipPlan extends Model implements Purchasable
{
    use SoftDeletes;

    public const IMAGE_URL = '/images/vip-subscription-gaming.webp';

    protected $fillable = [
        'code',
        'title',
        'description',
        'duration_days',
        'price_toman',
        'purchase_discount_percent',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price_toman' => 'integer',
        'purchase_discount_percent' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VipSubscription::class);
    }

    public function getPurchasableTitle(): string
    {
        return 'اشتراک VIP - '.$this->title;
    }

    public static function titleFromSnapshot(array $snapshot): string
    {
        $planTitle = trim((string) ($snapshot['plan_title'] ?? ''));

        if ($planTitle === '') {
            $legacyTitle = trim((string) ($snapshot['title'] ?? ''));
            $planTitle = trim((string) preg_replace('/^اشتراک VIP(?:\s*-\s*|\s+)/u', '', $legacyTitle));
        }

        return 'اشتراک VIP - '.($planTitle !== '' ? $planTitle : 'پلن ویژه');
    }

    public function getPurchasablePrice(): int
    {
        return (int) $this->price_toman;
    }

    public function isPurchasable(): bool
    {
        return $this->isPurchasableAvailable();
    }

    public function isPurchasableAvailable(): bool
    {
        return $this->exists && ! $this->trashed() && $this->is_active;
    }

    public function isDependentPurchasable(): bool
    {
        return false;
    }

    public function getPurchasableDependency(): ?array
    {
        return null;
    }

    public function getMaxQuantityPerOrder(): ?int
    {
        return 1;
    }

    public function getPurchasableSnapshot(): array
    {
        return [
            'type' => 'vip_plan',
            'title' => $this->getPurchasableTitle(),
            'plan_title' => $this->title,
            'image_url' => self::IMAGE_URL,
            'vip_plan_id' => $this->getKey(),
            'code' => $this->code,
            'duration_days' => (int) $this->duration_days,
            'price_toman' => (int) $this->price_toman,
            'max_quantity_per_order' => 1,
        ];
    }
}
