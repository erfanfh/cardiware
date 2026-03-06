<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('content');
            $table->string('status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->tinyInteger('rating_percent')->nullable();
            $table->unsignedBigInteger('likes_count')->default(0);
            $table->unsignedBigInteger('dislikes_count')->default(0);
            $table->bigInteger('score')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['commentable_type', 'commentable_id', 'parent_id', 'status'], 'comments_commentable_parent_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
