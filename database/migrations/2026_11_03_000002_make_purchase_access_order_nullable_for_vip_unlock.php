<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دسترسی امانیِ اشتراک ویژه (metadata.source = vip_borrow) سفارشی ندارد؛
 * برای ثبت آن order_id و order_item_id باید اختیاری باشند. کلیدهای خارجی و
 * یکتایی order_item_id سر جایشان می‌مانند (NULLها با هم تداخل ندارند).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_accesses', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->unsignedBigInteger('order_item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // ردیف‌های بدون سفارش (دسترسی امانی VIP) قبل از برگرداندن حذف می‌شوند.
        \Illuminate\Support\Facades\DB::table('purchase_accesses')
            ->whereNull('order_id')
            ->orWhereNull('order_item_id')
            ->delete();

        Schema::table('purchase_accesses', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->unsignedBigInteger('order_item_id')->nullable(false)->change();
        });
    }
};
