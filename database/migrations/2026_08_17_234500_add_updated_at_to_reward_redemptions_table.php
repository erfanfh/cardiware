<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        DB::table('reward_redemptions')
            ->whereNull('updated_at')
            ->update(['updated_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
