<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->char('idempotency_fingerprint', 64)
                ->after('idempotency_key')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex([
                'idempotency_fingerprint',
            ]);

            $table->dropColumn('idempotency_fingerprint');
        });
    }
};
