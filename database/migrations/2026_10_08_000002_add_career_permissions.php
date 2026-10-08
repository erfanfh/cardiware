<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PERMISSIONS = [
        ['career-position', 'list', 'مشاهده موقعیت‌های شغلی'],
        ['career-position', 'create', 'ایجاد موقعیت شغلی'],
        ['career-position', 'edit', 'ویرایش موقعیت شغلی'],
        ['career-position', 'delete', 'حذف موقعیت شغلی'],
        ['career-application', 'list', 'مشاهده درخواست‌های همکاری و رزومه‌ها'],
        ['career-application', 'review', 'بررسی درخواست‌های همکاری'],
        ['setting', 'careers', 'صفحه استخدام'],
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as [$group, $ability, $label]) {
            DB::table('permissions')->updateOrInsert(
                ['group' => $group, 'ability' => $ability],
                ['label' => $label, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    public function down(): void
    {
        foreach (self::PERMISSIONS as [$group, $ability]) {
            DB::table('permissions')->where('group', $group)->where('ability', $ability)->delete();
        }
    }
};
