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
        Schema::create('product_sale_item_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_sale_item_id');
            $table->unsignedBigInteger('product_attribute_value_id')->nullable();
            $table->unsignedBigInteger('product_custom_attribute_value_id')->nullable();
            $table->timestamps();

            $table->foreign('product_sale_item_id', 'psi_attr_values_item_fk')
                ->references('id')
                ->on('product_sale_items')
                ->cascadeOnDelete();
            $table->foreign('product_attribute_value_id', 'psi_attr_values_global_fk')
                ->references('id')
                ->on('product_attribute_values')
                ->cascadeOnDelete();
            $table->foreign('product_custom_attribute_value_id', 'psi_attr_values_custom_fk')
                ->references('id')
                ->on('product_custom_attribute_values')
                ->cascadeOnDelete();

            $table->index('product_sale_item_id', 'psi_attr_values_sale_item_id_index');
            $table->index('product_attribute_value_id', 'psi_attr_values_global_value_id_index');
            $table->index('product_custom_attribute_value_id', 'psi_attr_values_custom_value_id_index');

            $table->unique(
                ['product_sale_item_id', 'product_attribute_value_id'],
                'psi_attr_values_sale_item_global_unique'
            );
            $table->unique(
                ['product_sale_item_id', 'product_custom_attribute_value_id'],
                'psi_attr_values_sale_item_custom_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sale_item_attribute_values');
    }
};
