<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_transfers', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_token')->unique();
            $table->foreignId('sender_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('recipient_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('sender_wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('recipient_wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('status', 30)->index();
            $table->string('request_key', 160)->unique();
            $table->foreignId('debit_transaction_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->foreignId('credit_transaction_id')->nullable()->constrained('wallet_transactions')->restrictOnDelete();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['sender_user_id', 'created_at']);
            $table->index(['recipient_user_id', 'created_at']);
            $table->index(['sender_wallet_id', 'created_at']);
            $table->index(['recipient_wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transfers');
    }
};
