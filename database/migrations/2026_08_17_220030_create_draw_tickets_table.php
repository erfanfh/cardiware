<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();

            $table->foreignId('campaign_id')
                ->constrained('draw_campaigns')
                ->restrictOnDelete();
            $table->foreignId('ticket_grant_id')
                ->constrained('draw_ticket_grants')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('code', 64)->unique();
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('issued_at');

            $table->timestamp('invalidated_at')->nullable();
            $table->foreignId('invalidated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('invalidation_reason', 255)->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'status', 'id']);
            $table->index(['campaign_id', 'user_id', 'status']);
            $table->index(['ticket_grant_id', 'status']);
            $table->index(['user_id', 'status', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_tickets');
    }
};
