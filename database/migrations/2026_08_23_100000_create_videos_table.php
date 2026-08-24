<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('status', 30)->index();
            $table->string('source_type', 20)->index();
            $table->text('video_url');
            $table->string('aparat_video_id', 100)->nullable()->index();
            $table->foreignId('cover_media_file_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('comments_enabled')->default(true);
            $table->boolean('guest_comments_enabled')->default(true);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'published_at']);
        });

        Schema::create('video_slugs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('video_id')->constrained('videos')->cascadeOnDelete();
            $table->string('route_key')->unique();
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->index(['video_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_slugs');
        Schema::dropIfExists('videos');
    }
};
