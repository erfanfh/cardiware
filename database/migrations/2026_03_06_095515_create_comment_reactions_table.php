<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->binary('ip_hash')->nullable();
            $table->string('type');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id'], 'comment_reactions_comment_user_unique');
            $table->unique(['comment_id', 'ip_hash'], 'comment_reactions_comment_ip_unique');
            $table->index(['comment_id', 'type'], 'comment_reactions_comment_type_index');
            $table->index(['user_id', 'comment_id'], 'comment_reactions_user_comment_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
