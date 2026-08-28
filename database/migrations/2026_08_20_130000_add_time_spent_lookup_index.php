<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loyalty_time_spent_sessions', function (Blueprint $table) {
            $table->index([
                'user_id',
                'earning_rule_id',
                'target_type',
                'target_id',
                'created_at',
            ], 'loyalty_time_spent_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('loyalty_time_spent_sessions', function (Blueprint $table) {
            $table->dropIndex('loyalty_time_spent_lookup_index');
        });
    }
};
