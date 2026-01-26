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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users');
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt');
            $table->longText('body');
            $table->string('status')->default('draft');
            $table->dateTime('published_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'deleted_at']);
            $table->index(['status', 'published_at']);
            $table->index(['author_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
