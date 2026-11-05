<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * برترین‌های باشگاه در هر ماه، فصل و سال شمسی.
 * loyalty_period_closures: هر دوره فقط یک‌بار بسته می‌شود (حتی بدون برنده).
 * loyalty_champions: نفرات ۱ تا ۳ هر دوره و جوایزی که گرفته‌اند.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_period_closures', function (Blueprint $table): void {
            $table->id();
            $table->string('period_type', 10);
            $table->string('period_key', 20);
            $table->string('period_label', 80);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('closed_at');
            $table->unsignedTinyInteger('winners_count')->default(0);
            $table->timestamps();

            $table->unique(['period_type', 'period_key']);
        });

        Schema::create('loyalty_champions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('closure_id')->constrained('loyalty_period_closures')->cascadeOnDelete();
            $table->string('period_type', 10);
            $table->string('period_key', 20);
            $table->string('period_label', 80);
            $table->unsignedTinyInteger('rank');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('score');
            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code', 60)->nullable();
            $table->unsignedInteger('raffle_tickets')->default(0);
            // جایزه‌ی ویژه (مثلاً کنسول برای نفر اول سال) که دستی تحویل داده می‌شود.
            $table->string('grand_prize', 190)->nullable();
            $table->string('grand_prize_status', 20)->default('none');
            $table->timestamp('grand_prize_delivered_at')->nullable();
            $table->timestamps();

            $table->unique(['period_type', 'period_key', 'rank']);
            $table->index(['user_id', 'period_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_champions');
        Schema::dropIfExists('loyalty_period_closures');
    }
};
