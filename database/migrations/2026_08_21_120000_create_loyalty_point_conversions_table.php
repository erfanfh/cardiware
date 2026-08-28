<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_point_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('point_account_id')->constrained('point_accounts')->restrictOnDelete();
            $table->foreignId('wallet_id')->constrained('wallets')->restrictOnDelete();
            $table->foreignId('point_ledger_entry_id')
                ->nullable()
                ->unique()
                ->constrained('point_ledger_entries')
                ->restrictOnDelete();
            $table->foreignId('wallet_transaction_id')
                ->nullable()
                ->unique()
                ->constrained('wallet_transactions')
                ->restrictOnDelete();
            $table->unsignedBigInteger('points');
            $table->unsignedBigInteger('wallet_amount');
            $table->unsignedBigInteger('points_per_unit');
            $table->unsignedBigInteger('wallet_amount_per_unit');
            $table->string('idempotency_key', 160)->unique();
            $table->char('idempotency_fingerprint', 64);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_conversions');
    }
};
