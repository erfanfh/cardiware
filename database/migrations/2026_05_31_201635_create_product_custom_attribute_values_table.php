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
        Schema::create('product_custom_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_custom_attribute_id');
            $table->string('title');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_custom_attribute_id', 'pc_attr_values_attr_fk')
                ->references('id')
                ->on('product_custom_attributes')
                ->cascadeOnDelete();

            $table->index('product_custom_attribute_id', 'pc_attr_values_attribute_id_index');
            $table->index(
                ['product_custom_attribute_id', 'sort_order'],
                'pc_attr_values_attribute_sort_order_index'
            );
            $table->unique(
                ['product_custom_attribute_id', 'title'],
                'pc_attr_values_attribute_title_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_custom_attribute_values');
    }
};
