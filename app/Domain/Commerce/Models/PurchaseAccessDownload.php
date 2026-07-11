<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseAccessDownload extends Model
{
    protected $fillable = [
        'purchase_access_id',
        'user_id',
        'product_id',
        'product_sale_item_id',
        'product_sale_item_file_id',
        'file_name',
        'file_disk',
        'file_path',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'purchase_access_id' => 'integer',
        'user_id' => 'integer',
        'product_id' => 'integer',
        'product_sale_item_id' => 'integer',
        'product_sale_item_file_id' => 'integer',
        'metadata' => 'array',
    ];

    public function purchaseAccess(): BelongsTo
    {
        return $this->belongsTo(PurchaseAccess::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productSaleItem(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItem::class);
    }

    public function productSaleItemFile(): BelongsTo
    {
        return $this->belongsTo(ProductSaleItemFile::class);
    }
}
