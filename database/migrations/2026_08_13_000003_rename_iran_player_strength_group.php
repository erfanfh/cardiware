<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('launcher_mod_groups')
            ->where('key', 'iran_player_strength')
            ->update([
                'title' => 'سطح قدرت بازیکنان',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('launcher_mod_groups')
            ->where('key', 'iran_player_strength')
            ->update([
                'title' => 'سطح قدرت بازیکنان ایران',
                'updated_at' => now(),
            ]);
    }
};
