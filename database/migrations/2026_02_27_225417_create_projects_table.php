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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('status')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->string('current_version', 32)->nullable();
            $table->string('creator_name')->nullable();
            $table->string('platform')->nullable();
            $table->text('excerpt');
            $table->longText('body');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['slug', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
