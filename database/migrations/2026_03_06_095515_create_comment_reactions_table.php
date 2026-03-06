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
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments');
            $table->foreignId('user_id')->constrained('users');
            $table->binary('ip_hash')->nullable();
            $table->string('type');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->unique(['comment_id', 'ip_hash']);
            $table->index(['comment_id', 'type']);
            $table->index(['user_id', 'comment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
