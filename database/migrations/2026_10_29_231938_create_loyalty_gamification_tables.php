<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // زنجیره ورود روزانه هر کاربر
        Schema::create('loyalty_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('current_days')->default(0);
            $table->unsignedSmallInteger('longest_days')->default(0);
            $table->date('last_visit_date')->nullable();
            $table->date('current_streak_started_on')->nullable();
            $table->timestamps();
        });

        // مأموریت‌های دوره‌ای (هفتگی)
        Schema::create('loyalty_missions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->string('icon', 16)->nullable();
            $table->string('metric', 40);
            $table->unsignedSmallInteger('target_count')->default(1);
            $table->unsignedInteger('reward_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('loyalty_mission_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period_key', 16);
            $table->unsignedInteger('progress_count')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reward_entry_id')->nullable()->constrained('point_ledger_entries')->nullOnDelete();
            $table->timestamps();

            $table->unique(['loyalty_mission_id', 'user_id', 'period_key'], 'mission_progress_unique');
            $table->index(['user_id', 'period_key']);
        });

        // نشان‌های افتخار
        Schema::create('loyalty_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('title');
            $table->string('description', 500)->nullable();
            $table->string('badge', 16)->nullable();
            $table->string('metric', 40);
            $table->unsignedBigInteger('threshold')->default(1);
            $table->unsignedInteger('reward_points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('loyalty_achievement_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_achievement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('awarded_at')->useCurrent();
            $table->foreignId('reward_entry_id')->nullable()->constrained('point_ledger_entries')->nullOnDelete();
            $table->timestamps();

            $table->unique(['loyalty_achievement_id', 'user_id'], 'achievement_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_achievement_user');
        Schema::dropIfExists('loyalty_achievements');
        Schema::dropIfExists('loyalty_mission_progress');
        Schema::dropIfExists('loyalty_missions');
        Schema::dropIfExists('loyalty_streaks');
    }
};
