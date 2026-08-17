<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_runs', function (Blueprint $table) {
            $table->id();
            $table->string('public_token', 40)->unique();

            $table->foreignId('campaign_id')
                ->constrained('draw_campaigns')
                ->restrictOnDelete();

            $table->string('type', 30);
            $table->string('status', 30)->default('pending')->index();
            $table->string('algorithm', 80);

            $table->string('seed_commitment', 64)->nullable();
            $table->text('seed_ciphertext')->nullable();
            $table->text('seed_reveal')->nullable();

            $table->unsignedBigInteger('eligible_ticket_count')->default(0);
            $table->string('eligible_set_hash', 64)->nullable();

            $table->foreignId('started_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'type', 'status']);
            $table->index(['campaign_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_runs');
    }
};
