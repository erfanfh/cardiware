<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('body')->nullable();
            $table->longText('rules')->nullable();
            $table->foreignId('cover_media_file_id')
                ->nullable()
                ->constrained('media_files')
                ->nullOnDelete();

            $table->string('status', 30)->default('draft')->index();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->timestamp('draw_at')->nullable();

            $table->unsignedBigInteger('ticket_limit')->nullable();
            $table->unsignedBigInteger('ticket_limit_per_user')->nullable();

            $table->boolean('allow_multiple_wins_per_user')->default(false);
            $table->unsignedSmallInteger('reserve_winners_per_prize')->default(3);
            $table->unsignedInteger('winner_confirmation_window_hours')->nullable()->default(72);
            $table->boolean('show_winners_publicly')->default(false);

            $table->unsignedBigInteger('issued_tickets_count')->default(0);
            $table->unsignedBigInteger('active_tickets_count')->default(0);
            $table->unsignedBigInteger('participants_count')->default(0);

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('drawing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'closes_at']);
            $table->index(['status', 'draw_at']);
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_campaigns');
    }
};
