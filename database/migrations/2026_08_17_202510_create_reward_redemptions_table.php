<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->foreignId('reward_offer_id')
                ->constrained('reward_offers')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('point_ledger_entry_id')
                ->unique()
                ->constrained('point_ledger_entries')
                ->restrictOnDelete();
            $table->foreignId('coupon_id')
                ->nullable()
                ->unique()
                ->constrained('coupons')
                ->nullOnDelete();
            $table->unsignedBigInteger('points_spent');
            $table->string('idempotency_key', 160)->unique();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index(['reward_offer_id', 'user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
