<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('audience')->default('all');
            $table->unsignedInteger('delay_seconds')->default(0);
            $table->unsignedInteger('close_minutes')->default(60);
            $table->unsignedInteger('hide_days')->default(30);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popups');
    }
};
