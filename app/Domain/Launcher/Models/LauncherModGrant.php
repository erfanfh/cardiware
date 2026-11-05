<?php

namespace App\Domain\Launcher\Models;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * دسترسی دائمی یک کاربر به یک ماد پولی.
 *
 * source=purchase: خرید تکی (از مسیر سفارش؛ با مرجوعی/لغو سفارش حذف می‌شود).
 * source=vip_permanent: میراثیِ دوره‌ای که ماد نوع «اشتراک ویژه» داشت؛ دیگر
 * ساخته نمی‌شود ولی ردیف‌های موجود همچنان دسترسی دائمی می‌دهند.
 */
class LauncherModGrant extends Model
{
    public const SOURCE_PURCHASE = 'purchase';

    public const SOURCE_VIP_PERMANENT = 'vip_permanent';

    protected $fillable = [
        'user_id', 'launcher_mod_id', 'source', 'order_id', 'order_item_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mod(): BelongsTo
    {
        return $this->belongsTo(LauncherMod::class, 'launcher_mod_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
