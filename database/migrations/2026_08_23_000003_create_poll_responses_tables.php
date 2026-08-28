<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('identity_key', 80);
            $table->char('ip_hash', 64);
            $table->string('user_agent', 500)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['poll_id', 'identity_key']);
            $table->index(['poll_id', 'submitted_at']);
            $table->index(['poll_id', 'user_id']);
        });

        Schema::create('poll_response_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_response_id')->constrained('poll_responses')->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained('poll_options')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['poll_response_id', 'poll_option_id'], 'poll_response_option_unique');
            $table->index('poll_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_response_options');
        Schema::dropIfExists('poll_responses');
    }
};
