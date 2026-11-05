<?php

namespace App\Domain\Marketplace\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Enums\SellerPermissionEnum;
use App\Domain\Marketplace\Enums\StoreStatusEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Seo\Traits\HasSeoMeta;
use App\Domain\System\Interfaces\PageViewableInterface;
use App\Infrastructure\Traits\HasExcerpt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model implements PageViewableInterface
{
    use HasExcerpt, HasSeoMeta, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'activity_field',
        'hide_sales_count',
        'seller_permissions',
        'picture_id',
        'status',
    ];

    protected $casts = [
        'hide_sales_count' => 'boolean',
        'seller_permissions' => 'array',
        'status' => StoreStatusEnum::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** فروشگاهِ بدون نامک (داده‌ی ناقص) با شناسه آدرس‌دهی می‌شود تا صفحه‌های پنل خطای ۵۰۰ ندهند. */
    public function getRouteKey(): mixed
    {
        return filled($this->slug) ? $this->slug : (string) $this->getKey();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $store = parent::resolveRouteBinding($value, $field);

        if ($store === null && $field === null && ctype_digit((string) $value)) {
            $store = $this->newQuery()
                ->whereKey((int) $value)
                ->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', ''))
                ->first();
        }

        return $store;
    }

    public function sellerCan(SellerPermissionEnum|string $permission): bool
    {
        $permission = is_string($permission) ? SellerPermissionEnum::tryFrom($permission) : $permission;
        if (! $permission) {
            return false;
        }

        // Missing settings preserve access for existing stores. Unknown values fail closed.
        $settings = $this->seller_permissions ?? [];
        $enabled = fn (SellerPermissionEnum $item): bool => ! array_key_exists($item->value, $settings)
            || in_array($settings[$item->value], [true, 1, '1'], true);

        if (! $enabled(SellerPermissionEnum::ACCESS_PANEL) || ! $enabled($permission)) {
            return false;
        }

        foreach ($permission->dependencies() as $dependency) {
            if (! $this->sellerCan($dependency)) {
                return false;
            }
        }

        return true;
    }

    public function getExcerptAttribute(): string
    {
        return '';
    }

    public function picture(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'picture_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(StoreUser::class);
    }

    public function sellers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(StoreWithdrawal::class);
    }
}
