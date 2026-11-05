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
        'user_id', 'launcher_device_id', 'product_sale_item_id', 'launcher_mod_id', 'state_hash',
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
    public function mod(): BelongsTo { return $this->belongsTo(LauncherMod::class, 'launcher_mod_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }

    /**
     * بعد از پایان مهلت شروع خرید، تا یک روز هنوز می‌شود نتیجه را به لانچر رساند:
     * ورود به سایت + پرداخت در درگاه ممکن است بیشتر از مهلت طول بکشد و consume خودش
     * چیزی نمی‌دهد (دسترسی را فقط پرداخت موفق می‌سازد).
     */
    public const RESULT_GRACE_HOURS = 24;

    public function canDeliverResult(): bool
    {
        return $this->consumed_at === null
            && $this->expires_at !== null
            && $this->expires_at->copy()->addHours(self::RESULT_GRACE_HOURS)->isFuture();
    }

    /** این سفارش همان چیزی را خریده که این نشست برایش شروع شده (ماد تکی یا نسخه‌ی محصول). */
    public function isPaidBy(Order $order): bool
    {
        $items = $order->items();

        return $this->launcher_mod_id !== null
            ? $items->where('purchasable_type', LauncherMod::MORPH_ALIAS)
                ->where('purchasable_id', $this->launcher_mod_id)
                ->exists()
            : $items->where('purchasable_type', 'product_sale_item')
                ->where('purchasable_id', $this->product_sale_item_id)
                ->exists();
    }
}
