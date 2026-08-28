<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'list' => 'لیست نظرسنجی‌ها',
            'create' => 'ایجاد نظرسنجی',
            'edit' => 'ویرایش نظرسنجی',
            'delete' => 'حذف نظرسنجی',
            'analytics' => 'مشاهده آمار نظرسنجی',
            'export' => 'دریافت خروجی پاسخ‌های نظرسنجی',
        ];

        foreach ($permissions as $ability => $label) {
            DB::table('permissions')->updateOrInsert(
                ['group' => 'poll', 'ability' => $ability],
                ['label' => $label, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('group', 'poll')->delete();
    }
};
