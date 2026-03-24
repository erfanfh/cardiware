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
        Schema::create('page_layout_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_layout_area_id')->constrained('page_layout_areas')->cascadeOnDelete();
            $table->string('key');
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['page_layout_area_id', 'key']);
            $table->index(['page_layout_area_id', 'sort_order']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_layout_elements');
    }
};
