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
        Schema::create('product_attribute_assignment_values', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_attribute_assignment_id');
            $table->foreignId('product_attribute_value_id');

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(
                ['product_attribute_assignment_id', 'product_attribute_value_id'],
                'paav_assignment_value_unique'
            );

            $table->index('product_attribute_assignment_id', 'paav_assignment_id_index');
            $table->index('product_attribute_value_id', 'paav_value_id_index');
            $table->index('sort_order', 'paav_sort_order_index');

            $table
                ->foreign('product_attribute_assignment_id', 'paav_assignment_id_foreign')
                ->references('id')
                ->on('product_attribute_assignments')
                ->cascadeOnDelete();

            $table
                ->foreign('product_attribute_value_id', 'paav_value_id_foreign')
                ->references('id')
                ->on('product_attribute_values')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_assignment_values');
    }
};
