<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * عنوان کوتاهٔ پروژه برای نمایش در کارت صفحهٔ اصلی و سایدبار:
 * عنوان اصلی ممکن است بسیار طولانی باشد و استایل کارت را خراب کند.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('short_title', 120)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('short_title'));
    }
};
