<?php

namespace App\Domain\Commerce\Models;

use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseAccessDownload extends Model
{
    protected $fillable = [
        'purchase_access_id',
        'user_id',
        'launcher_device_id',
        'product_id',
        'product_sale_item_id',
        'product_sale_item_file_id',
        'download_session_id',
        'manifest_revision',
        'file_sha256',
        'file_size_bytes',
        'session_expires_at',
        'invalidated_at',
        'last_prepared_at',
        'prepare_count',
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
        'launcher_device_id' => 'integer',
        'product_id' => 'integer',
        'product_sale_item_id' => 'integer',
        'product_sale_item_file_id' => 'integer',
        'manifest_revision' => 'integer',
        'file_size_bytes' => 'integer',
        'session_expires_at' => 'datetime',
        'invalidated_at' => 'datetime',
        'last_prepared_at' => 'datetime',
        'prepare_count' => 'integer',
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

    public function launcherDevice(): BelongsTo
    {
        return $this->belongsTo(LauncherDevice::class);
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
