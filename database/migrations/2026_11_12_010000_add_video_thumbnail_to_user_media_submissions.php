<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تصویر شاخص اختیاری برای ویدیوهای رسانه‌های کاربران: وقتی مدیر لینک دانلود
 * مستقیم می‌گذارد (بدون embed)، این تصویر به‌عنوان کاور ویدیو در گالری نمایش
 * داده می‌شود تا باکس تصویر خالی نماند.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_media_submissions', function (Blueprint $table) {
            $table->string('video_thumbnail_url', 1000)->nullable()->after('video_url');
        });
    }

    public function down(): void
    {
        Schema::table('user_media_submissions', fn (Blueprint $table) => $table->dropColumn('video_thumbnail_url'));
    }
};
