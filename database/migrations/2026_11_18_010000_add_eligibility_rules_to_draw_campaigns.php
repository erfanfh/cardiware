<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * شرط‌های واجد شرایط بودن برای قرعه‌کشی:
 * eligibility_rules = JSON آرایه‌ای از قوانین
 * مثال:
 * {
 *   "min_referrals": 3,          // حداقل تعداد دعوت ثبت‌نام‌شده
 *   "min_referral_orders": 1,     // حداقل خرید توسط دعوت‌شدگان
 *   "min_xp": 200,               // حداقل امتیاز تجربه باشگاه
 *   "min_comments": 5,           // حداقل دیدگاه تأییدشده
 *   "min_own_orders": 1,         // حداقل خرید شخصی
 *   "min_account_days": 30,      // حداقل عمر حساب (روز)
 *   "require_verified_mobile": true, // موبایل تأییدشده الزامی
 *   "min_approved_media": 1,     // حداقل رسانه تأییدشده
 *   "require_vip": true         // اشتراک VIP فعال الزامی
 * }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_campaigns', function (Blueprint $table) {
            $table->json('eligibility_rules')->nullable()->after('ticket_price');
        });
    }

    public function down(): void
    {
        Schema::table('draw_campaigns', fn (Blueprint $table) => $table->dropColumn('eligibility_rules'));
    }
};
