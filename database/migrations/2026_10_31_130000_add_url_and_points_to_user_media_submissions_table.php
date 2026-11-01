<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_media_submissions', function (Blueprint $table) {
            // آپلود مستقیم حذف شد؛ کاربر لینک تصویر آپلودشده در سرویس‌های خارجی (پیکوفایل و…) را می‌فرستد
            $table->string('image_url')->nullable()->after('image_path');
            // امتیازی که بابت این ارسال به کاربر داده شده (پس از تأیید مدیر)
            $table->unsignedInteger('points_awarded')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('user_media_submissions', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'points_awarded']);
        });
    }
};
