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
        Schema::create('product_release_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_release_id')->constrained()->cascadeOnDelete();
            $table->text('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_special')->default(false);
            $table->timestamps();

            $table->index(['product_release_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_release_changes');
    }
};
