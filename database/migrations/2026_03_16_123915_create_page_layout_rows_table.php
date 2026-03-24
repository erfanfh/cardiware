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
        Schema::create('page_layout_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_layout_id')->constrained('page_layouts')->cascadeOnDelete();
            $table->string('key');
            $table->string('layout_type')->default('single');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->unique(['page_layout_id', 'key']);
            $table->index(['page_layout_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_layout_rows');
    }
};
