<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_holds', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('type', 40)->index();
            $table->string('status', 30)->default('active')->index();
            $table->string('debit_policy', 50);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('withdrawable_amount')->default(0);
            $table->nullableMorphs('reference');
            $table->foreignId('captured_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->restrictOnDelete();
            $table->foreignId('captured_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('released_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('idempotency_key', 160)->unique();
            $table->char('idempotency_fingerprint', 64)->index();
            $table->text('description')->nullable();
            $table->text('release_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status', 'created_at']);
            $table->index(['wallet_id', 'status', 'expires_at']);
            $table->index(['type', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_holds');
    }
};
