<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_accounts', function (Blueprint $table) {
            // XP مستقل از Points؛ فقط تعیین سطح. با خرج کردن یا پاداش سطح کاهش نمی‌یابد.
            $table->unsignedBigInteger('lifetime_xp')->default(0)->after('lifetime_earned');
        });

        // بک‌فیل: XP کاربران فعلی = کل امتیاز کسب‌شده تا امروز
        DB::table('point_accounts')->update([
            'lifetime_xp' => DB::raw('lifetime_earned'),
        ]);
    }

    public function down(): void
    {
        Schema::table('point_accounts', function (Blueprint $table) {
            $table->dropColumn('lifetime_xp');
        });
    }
};
