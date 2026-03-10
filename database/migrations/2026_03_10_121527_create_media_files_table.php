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
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('visibility')->default('public');
            $table->string('title');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->string('status')->default('processing');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['type']);
            $table->index(['visibility']);
            $table->index(['created_by']);
            $table->index(['status', 'processed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
