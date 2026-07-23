<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type', 40)->index();
            $table->string('direction', 20)->index();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('withdrawable_amount')->default(0);
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->unsignedBigInteger('withdrawable_balance_before');
            $table->unsignedBigInteger('withdrawable_balance_after');
            $table->nullableMorphs('reference');
            $table->string('idempotency_key', 160)->unique();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['wallet_id', 'created_at']);
            $table->index(['wallet_id', 'type', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
