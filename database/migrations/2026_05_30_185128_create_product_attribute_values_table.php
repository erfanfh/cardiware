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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();

            $table->tinyInteger('active_unique_key')->storedAs('CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END');

            $table->unique(
                ['product_attribute_id', 'title', 'active_unique_key'],
                'product_attribute_values_active_title_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
