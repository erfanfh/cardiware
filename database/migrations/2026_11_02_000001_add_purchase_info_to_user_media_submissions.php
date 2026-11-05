<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_media_submissions', function (Blueprint $table) {
            // اطلاعات خرید ارسال‌کننده (اختیاری) برای اعتبارسنجی رسانه‌های خریداران
            $table->string('order_number', 64)->nullable()->after('description');
            $table->string('purchaser_mobile', 20)->nullable()->after('order_number');
        });
    }

    public function down(): void
    {
        Schema::table('user_media_submissions', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'purchaser_mobile']);
        });
    }
};
