<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * بستهٔ قابلیت‌های نوامبر ۲۰۲۶:
 *  - مدیریت بیو VIP (وضعیت تایید + زمان آخرین ویرایش)
 *  - درصد تخفیف خرید تکی به‌ازای پلن اشتراک VIP
 *  - هشدار فعال‌سازی ماد در لانچر (متن + سطح)
 *  - دستور بازکردن تنظیمات بازی (مسیر exe نسبت به ریشهٔ بازی)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // none = هرگز نوشته | pending = در انتظار تایید | approved | rejected
            $table->string('club_bio_status', 20)->default('none')->after('club_bio');
            $table->timestamp('club_bio_edited_at')->nullable()->after('club_bio_status');
        });

        // Existing introductions also require review before becoming public.
        DB::table('users')->whereNotNull('club_bio')->where('club_bio', '!=', '')->update([
            'club_bio_status' => 'pending',
            'club_bio_edited_at' => now(),
        ]);

        Schema::table('vip_plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('purchase_discount_percent')->nullable()->after('price_toman');
        });

        Schema::table('launcher_mods', function (Blueprint $table) {
            $table->text('warning_text')->nullable()->after('is_required');
            $table->string('warning_level', 20)->default('warning')->after('warning_text');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('game_settings_command', 255)->nullable()->after('content_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['club_bio_status', 'club_bio_edited_at']));
        Schema::table('vip_plans', fn (Blueprint $table) => $table->dropColumn('purchase_discount_percent'));
        Schema::table('launcher_mods', fn (Blueprint $table) => $table->dropColumn(['warning_text', 'warning_level']));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('game_settings_command'));
    }
};
