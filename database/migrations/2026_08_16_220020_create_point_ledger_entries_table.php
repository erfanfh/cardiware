<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->foreignId('point_account_id')
                ->constrained('point_accounts')
                ->restrictOnDelete();
            $table->foreignId('earning_rule_id')
                ->nullable()
                ->constrained('earning_rules')
                ->restrictOnDelete();
            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('type', 30)->index();
            $table->string('direction', 20)->index();
            $table->string('reason', 80)->index();
            $table->unsignedBigInteger('amount');

            // Signed because point debt is intentionally supported for reversals.
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');

            $table->nullableMorphs('reference');

            $table->foreignId('reversal_of_id')
                ->nullable()
                ->constrained('point_ledger_entries')
                ->restrictOnDelete();

            $table->string('idempotency_key', 160)->unique();
            $table->string('idempotency_fingerprint', 64);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['point_account_id', 'created_at']);
            $table->index(['point_account_id', 'type', 'created_at']);
            $table->index(['point_account_id', 'reason', 'created_at']);
            $table->index(['earning_rule_id', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
            $table->index(['reversal_of_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_ledger_entries');
    }
};
