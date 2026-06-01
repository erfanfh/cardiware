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
        Schema::create('product_sale_item_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('product_sale_item_id');
            $table->index(['product_sale_item_id', 'sort_order'], 'psi_files_item_sort_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sale_item_files');
    }
};
