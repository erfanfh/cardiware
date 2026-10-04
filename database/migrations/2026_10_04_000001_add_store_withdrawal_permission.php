<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['group' => 'store', 'ability' => 'withdrawals'],
            ['label' => 'بررسی درخواست‌های وجه فروشگاه‌ها', 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('group', 'store')->where('ability', 'withdrawals')->delete();
    }
};
