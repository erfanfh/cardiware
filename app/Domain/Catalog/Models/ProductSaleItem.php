<?php

namespace App\Domain\Catalog\Models;

use App\Application\Commerce\Actions\Pricing\ResolvePurchasablePriceAction;
use App\Application\Commerce\Contracts\ProductReportablePurchasable;
use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModGroup;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sellable item of a product.
 */
class ProductSaleItem extends Model implements Purchasable, ProductReportablePurchasable
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'title',
        'edition_key',
        'version',
        'manifest_revision',
        'launcher_enabled',
        'launcher_title',
        'launcher_excerpt',
        'launcher_cover_media_id',
        'launcher_sort_order',
        'launcher_support_enabled',
        'launcher_support_url',
        'base_price',
        'status',
        'download_limit',
        'download_expires_in_days',
        'max_quantity_per_order',
        'description',
        'sort_order',
    ];

    protected $casts = [
        'manifest_revision' => 'integer',
        'launcher_enabled' => 'boolean',
        'launcher_sort_order' => 'integer',
        'launcher_support_enabled' => 'boolean',
        'base_price' => 'integer',
        'download_limit' => 'integer',
        'download_expires_in_days' => 'integer',
        'max_quantity_per_order' => 'integer',
        'sort_order' => 'integer',
        'status' => ProductSaleItemStatusEnum::class,
    ];

    /**
     * Get the product that owns the sale item.
     *
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the Hero image used specifically for this sale item in MW Launcher.
     *
     * The legacy database column name is intentionally kept for now.
     */
    public function launcherHero(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'launcher_cover_media_id');
    }

    /**
     * Get the downloadable files of the sale item.
     *
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProductSaleItemFile::class)
            ->orderBy('sort_order');
    }

    /**
     * Get the selected attribute values of the sale item.
     *
     * @return HasMany
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductSaleItemAttributeValue::class);
    }

    /**
     * Get the current special discount of this sale item.
     */
    public function discount(): HasOne
    {
        return $this->hasOne(ProductSaleItemDiscount::class);
    }

    /**
     * Get purchase accesses for this sale item.
     */
    public function purchaseAccesses(): HasMany
    {
        return $this->hasMany(PurchaseAccess::class);
    }

    /**
     * Get the purchasable display title.
     */
    public function getPurchasableTitle(): string
    {
        return $this->product?->title ?? 'آیتم فروش محصول';
    }

    /**
     * Get the current final purchasable price in toman.
     *
     * This price includes active product sale item discount.
     */
    public function getPurchasablePrice(): int
    {
        return app(ResolvePurchasablePriceAction::class)
            ->execute($this)
            ->finalPrice;
    }

    /**
     * Determine whether this item can be purchased.
     *
     * This checks only the sale item status.
     * For full product + sale item availability, use isPurchasableAvailable().
     */
    public function isPurchasable(): bool
    {
        return $this->status === ProductSaleItemStatusEnum::AVAILABLE;
    }

    /**
     * Determine whether this item is dependent on another purchasable item.
     */
    public function isDependentPurchasable(): bool
    {
        return false;
    }

    /**
     * Get dependency data for dependent purchasable items.
     *
     * @return array<string, mixed>|null
     */
    public function getPurchasableDependency(): ?array
    {
        return null;
    }

    /**
     * Get the maximum quantity allowed per order.
     */
    public function getMaxQuantityPerOrder(): ?int
    {
        return $this->max_quantity_per_order;
    }

    /**
     * Get snapshot data for cart/order display.
     *
     * @return array<string, mixed>
     */
    public function getPurchasableSnapshot(): array
    {
        $price = app(ResolvePurchasablePriceAction::class)->execute($this);

        return [
            'type' => 'product_sale_item',
            'title' => $this->getPurchasableTitle(),
            'product_id' => $this->product_id,
            'store_id' => $this->product?->store_id,
            'image_url' => $this->product?->picture?->url,
            'product_title' => $this->product?->title,
            'product_sale_item_id' => $this->id,
            'product_sale_item_title' => $this->title ?? null,
            'sku' => $this->sku,
            'max_quantity_per_order' => $this->max_quantity_per_order,
            'pricing' => $price->toSnapshot(),
        ];
    }

    /**
     * Determine whether this sale item and its product are available for purchase.
     */
    public function isPurchasableAvailable(): bool
    {
        if ($this->status !== ProductSaleItemStatusEnum::AVAILABLE) {
            return false;
        }

        $product = $this->product()
            ->withoutGlobalScopes()
            ->withTrashed()
            ->first();

        if (!$product) {
            return false;
        }

        if (method_exists($product, 'trashed') && $product->trashed()) {
            return false;
        }

        return $product->status === ProductStatusEnum::ACTIVE;
    }

    public function getReportProductId(): ?int
    {
        return $this->product_id;
    }

    public function getReportStoreId(): ?int
    {
        return $this->product?->store_id;
    }

    public function manifest(): HasOne
    {
        return $this->hasOne(ProductSaleItemManifest::class);
    }

    public function launcherUpdates(): HasMany
    {
        return $this->hasMany(LauncherUpdate::class)->orderBy('sort_order')->orderBy('id');
    }

    public function launcherModGroups(): HasMany
    {
        return $this->hasMany(LauncherModGroup::class)->orderBy('sort_order')->orderBy('id');
    }

    public function launcherMods(): HasMany
    {
        return $this->hasMany(LauncherMod::class)->orderBy('priority')->orderBy('id');
    }
}
