<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_medal_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_medal_id')->constrained('product_medals')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['product_id', 'product_medal_id'],
                'product_medal_assignments_unique'
            );

            $table->index(
                ['product_id', 'sort_order'],
                'product_medal_assignments_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_medal_assignments');
    }
};
