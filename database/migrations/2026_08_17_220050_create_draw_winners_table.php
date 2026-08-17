<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_winners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained('draw_campaigns')
                ->restrictOnDelete();
            $table->foreignId('draw_run_id')
                ->constrained('draw_runs')
                ->restrictOnDelete();
            $table->foreignId('prize_id')
                ->constrained('draw_prizes')
                ->restrictOnDelete();
            $table->foreignId('ticket_id')
                ->constrained('draw_tickets')
                ->restrictOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('kind', 20);
            $table->unsignedSmallInteger('position');
            $table->string('status', 30)->default('pending')->index();

            $table->timestamp('selected_at');

            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('promoted_at')->nullable();
            $table->foreignId('promoted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('replaces_winner_id')
                ->nullable()
                ->constrained('draw_winners')
                ->nullOnDelete();

            $table->timestamp('delivered_at')->nullable();
            $table->foreignId('delivered_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('delivery_note')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['draw_run_id', 'ticket_id']);
            $table->unique(
                ['draw_run_id', 'prize_id', 'kind', 'position'],
                'draw_winners_run_prize_kind_position_unique'
            );
            $table->index(['campaign_id', 'status']);
            $table->index(['prize_id', 'kind', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_winners');
    }
};
