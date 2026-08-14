<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launcher_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type', 20)->default('info');
            $table->string('action_text', 80)->nullable();
            $table->text('action_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['is_enabled', 'published_at', 'expires_at', 'sort_order'], 'launcher_notifications_visible');
        });

        Schema::create('launcher_notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launcher_notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->unique(['launcher_notification_id', 'user_id'], 'launcher_notification_user_unique');
        });

        Schema::create('launcher_guides', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('summary', 500)->nullable();
            $table->longText('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(false);
            $table->timestamps();
            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_notification_reads');
        Schema::dropIfExists('launcher_notifications');
        Schema::dropIfExists('launcher_guides');
    }
};
