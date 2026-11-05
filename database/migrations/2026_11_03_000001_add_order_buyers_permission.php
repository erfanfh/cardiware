<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['group' => 'order', 'ability' => 'buyers'],
            ['label' => 'لیست خریداران (بدون مبالغ)', 'updated_at' => now(), 'created_at' => now()],
        );
    }

    public function down(): void
    {
        DB::table('permissions')->where('group', 'order')->where('ability', 'buyers')->delete();
    }
};
