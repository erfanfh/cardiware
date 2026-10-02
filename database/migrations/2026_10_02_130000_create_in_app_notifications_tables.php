<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_event_settings', function (Blueprint $table): void {
            $table->string('event_key', 64)->primary();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_key', 64);
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('url', 2048)->nullable();
            $table->timestamp('sent_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event_key', 'subject_type', 'subject_id'], 'user_notification_once');
            $table->index(['user_id', 'read_at', 'sent_at'], 'user_notifications_inbox');
            $table->index(['user_id', 'sent_at', 'id'], 'user_notifications_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
        Schema::dropIfExists('notification_event_settings');
    }
};
