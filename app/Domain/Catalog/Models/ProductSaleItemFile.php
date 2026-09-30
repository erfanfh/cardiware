<?php

namespace App\Domain\Catalog\Models;

use App\Domain\Commerce\Models\PurchaseAccessDownload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a downloadable file link for a product sale item.
 */
class ProductSaleItemFile extends Model
{
    protected $fillable = [
        'product_sale_item_id',
        'title',
        'file_name',
        'url',
        'mirror_url',
        'size_bytes',
        'sha256',
        'sha256_signature',
        'signature_key_id',
        'mime_type',
        'part_number',
        'archive_group',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected $hidden = ['url', 'mirror_url'];

    protected $casts = [
        'size_bytes' => 'integer',
        'part_number' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the sale item that owns the file.
     *
     * @return BelongsTo
     */
    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id');
    }

    /**
     * Get download logs for this file.
     */
    public function purchaseAccessDownloads(): HasMany
    {
        return $this->hasMany(PurchaseAccessDownload::class);
    }
}
