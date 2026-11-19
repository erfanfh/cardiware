<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قابلیت‌های VIP قرعه‌کشی:
 * - vip_monthly_ticket: هر ماه ۱ بلیط رایگان برای اعضای VIP فعال (هدیه اشتراک)
 * (شرط require_vip داخل eligibility_rules JSON ذخیره می‌شود و ستون لازم ندارد.)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_campaigns', function (Blueprint $table): void {
            $table->boolean('vip_monthly_ticket')->default(false)->after('points_per_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('draw_campaigns', function (Blueprint $table): void {
            $table->dropColumn('vip_monthly_ticket');
        });
    }
};
