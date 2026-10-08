<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['group' => 'audit', 'ability' => 'view'],
            ['label' => 'مشاهده تاریخچه تغییرات سامانه', 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('group', 'audit')->where('ability', 'view')->delete();
    }
};
