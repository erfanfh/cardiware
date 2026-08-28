<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('point_ledger_entries', function (Blueprint $table) {
            $table->timestamp('occurred_at')
                ->nullable()
                ->after('metadata')
                ->index();
        });

        DB::table('point_ledger_entries')
            ->whereNull('occurred_at')
            ->update([
                'occurred_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('point_ledger_entries', function (Blueprint $table) {
            $table->dropIndex(['occurred_at']);
            $table->dropColumn('occurred_at');
        });
    }
};
