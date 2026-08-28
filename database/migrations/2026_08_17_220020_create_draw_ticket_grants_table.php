<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_ticket_grants', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();

            $table->foreignId('campaign_id')
                ->constrained('draw_campaigns')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->nullableMorphs('source');

            $table->string('idempotency_key', 160)->unique();
            $table->string('idempotency_fingerprint', 64);

            $table->foreignId('issued_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('issued_at');

            $table->timestamp('invalidated_at')->nullable();
            $table->foreignId('invalidated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('invalidation_reason', 255)->nullable();

            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['campaign_id', 'user_id', 'created_at']);
            $table->index(['campaign_id', 'invalidated_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_ticket_grants');
    }
};
