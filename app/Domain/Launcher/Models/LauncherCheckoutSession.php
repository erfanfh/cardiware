<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\Order;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LauncherCheckoutSession extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id', 'launcher_device_id', 'product_sale_item_id', 'state_hash',
        'expires_at', 'consumed_at', 'order_id',
    ];

    protected $hidden = ['state_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function device(): BelongsTo { return $this->belongsTo(LauncherDevice::class, 'launcher_device_id'); }
    public function saleItem(): BelongsTo { return $this->belongsTo(ProductSaleItem::class, 'product_sale_item_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
