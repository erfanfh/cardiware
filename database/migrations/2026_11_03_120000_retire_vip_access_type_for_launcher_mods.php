<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ماد «اشتراک ویژه» حذف شد: اشتراک فقط خودِ محصول را باز می‌کند و مادها
 * یا رایگان‌اند یا خرید تکی. مادهای vip موجود رایگان می‌شوند (برای همه‌ی
 * دارندگان محصول، از جمله کاربران VIP، باز می‌مانند).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('launcher_mods')) {
            return;
        }

        DB::table('launcher_mods')
            ->where('access_type', 'vip')
            ->update(['access_type' => 'free', 'price_toman' => null]);

        if (Schema::hasColumn('launcher_mods', 'vip_unlock_mode')) {
            Schema::table('launcher_mods', function ($table): void {
                $table->dropColumn('vip_unlock_mode');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('launcher_mods') && ! Schema::hasColumn('launcher_mods', 'vip_unlock_mode')) {
            Schema::table('launcher_mods', function ($table): void {
                $table->string('vip_unlock_mode', 20)->default('subscription')->after('price_toman');
            });
        }
    }
};
