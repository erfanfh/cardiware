<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('content');
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedTinyInteger('rating_percent')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('dislikes_count')->default(0);
            $table->bigInteger('score')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_index');
            $table->index(['commentable_type', 'commentable_id', 'parent_id', 'status'], 'comments_commentable_parent_status_index');
            $table->index(['parent_id', 'status', 'created_at'], 'comments_parent_status_created_at_index');
            $table->index(['status', 'created_at'], 'comments_status_created_at_index');
            $table->index(['user_id'], 'comments_user_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
