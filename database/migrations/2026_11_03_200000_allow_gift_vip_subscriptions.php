<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * اشتراک VIP هدیه (پاداش باشگاه مثل ورود ۹۰ روز پشت‌سرهم، یا هدیه مدیر) سفارش
 * و پلن ندارد؛ source نشان می‌دهد از کجا آمده و gift_key جلوی هدیه تکراری را می‌گیرد.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vip_subscriptions', function (Blueprint $table): void {
            $table->unsignedBigInteger('vip_plan_id')->nullable()->change();
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->unsignedBigInteger('order_item_id')->nullable()->change();
            $table->string('source', 30)->default('order')->after('order_item_id');
            $table->string('gift_key', 160)->nullable()->unique()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('vip_subscriptions', function (Blueprint $table): void {
            $table->dropUnique(['gift_key']);
            $table->dropColumn(['source', 'gift_key']);
        });
    }
};
