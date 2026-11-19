<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * پیش‌فروش و «به‌زودی» برای محصولات:
 * - release_mode: normal | presale | coming_soon
 * - release_at: تاریخ/ساعت انتشار (برای تایمر شمارش معکوس)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('release_mode', 20)->default('normal')->after('status');
            $table->timestamp('release_at')->nullable()->after('release_mode');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['release_mode', 'release_at']));
    }
};
