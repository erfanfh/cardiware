<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // پیش‌فرض کمیسیون همکاری در فروش از ۲۵٪ به ۱۱٪ تغییر کرد؛
        // فقط مقداری که هنوز روی پیش‌فرض قدیمی است به‌روز می‌شود تا انتخاب دستی مدیر بازنویسی نشود.
        DB::table('settings')
            ->where('group', 'affiliate')
            ->where('key', 'default_commission_value')
            ->where('value', '"25"')
            ->update(['value' => '"11"', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'affiliate')
            ->where('key', 'default_commission_value')
            ->where('value', '"11"')
            ->update(['value' => '"25"', 'updated_at' => now()]);
    }
};
