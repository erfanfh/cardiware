<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loyalty_time_spent_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('earning_rule_id')
                ->nullable()
                ->constrained('earning_rules')
                ->nullOnDelete();

            $table->string('token')->unique();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');

            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();

            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('status')->default('pending');

            $table->timestamps();

            $table->index(['user_id', 'target_type', 'target_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_time_spent_sessions');
    }
};
