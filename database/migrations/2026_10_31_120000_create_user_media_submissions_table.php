<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_media_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20); // image | video
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable(); // فایل تصویر آپلودشده
            $table->string('video_url')->nullable();  // لینک embed ویدیو (آپارات/یوتیوب)
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_media_submissions');
    }
};
