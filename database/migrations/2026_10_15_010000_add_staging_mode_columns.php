<?php

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Launcher\Models\LauncherUpdate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staging / «حالت آماده‌سازی» for launcher updates.
 *
 *  - launcher_updates.is_staging  : این آپدیت فقط به حساب‌های تست نشان داده شود، نه کاربرِ عادی.
 *  - product_sale_items.staging_user_ids : لیستِ id حساب‌های تست (استثنا) برای همین محصول.
 *
 * نامِ هر دو جدول از خودِ مدل گرفته می‌شود تا با هر نام‌گذاری درست کار کند. idempotent است.
 * اگر در ساختارِ شما staging_user_ids بهتر است روی جدولِ manifest بنشیند، ستون را آن‌جا بساز
 * و در مدلِ مربوطه fillable/cast کن (منطق یکسان است).
 */
return new class extends Migration {
    public function up(): void
    {
        $updates = (new LauncherUpdate())->getTable();
        Schema::table($updates, function (Blueprint $table) use ($updates): void {
            if (!Schema::hasColumn($updates, 'is_staging')) {
                $table->boolean('is_staging')->default(false)->after('is_enabled');
            }
        });

        $saleItems = (new ProductSaleItem())->getTable();
        Schema::table($saleItems, function (Blueprint $table) use ($saleItems): void {
            if (!Schema::hasColumn($saleItems, 'staging_user_ids')) {
                $table->json('staging_user_ids')->nullable();
            }
        });
    }

    public function down(): void
    {
        $updates = (new LauncherUpdate())->getTable();
        Schema::table($updates, function (Blueprint $table) use ($updates): void {
            if (Schema::hasColumn($updates, 'is_staging')) {
                $table->dropColumn('is_staging');
            }
        });

        $saleItems = (new ProductSaleItem())->getTable();
        Schema::table($saleItems, function (Blueprint $table) use ($saleItems): void {
            if (Schema::hasColumn($saleItems, 'staging_user_ids')) {
                $table->dropColumn('staging_user_ids');
            }
        });
    }
};
