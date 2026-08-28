<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table): void {
            $table->id();
            $table->string('public_token', 40)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('question');
            $table->longText('description')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->string('audience', 20)->default('everyone')->index();
            $table->string('results_visibility', 20)->default('after_vote');
            $table->boolean('allows_multiple_selections')->default(false);
            $table->unsignedSmallInteger('min_selections')->default(1);
            $table->unsignedSmallInteger('max_selections')->default(1);
            $table->boolean('allow_vote_changes')->default(false);
            $table->boolean('randomize_options')->default(false);
            $table->boolean('show_response_count')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('responses_count')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('polls');
    }
};
