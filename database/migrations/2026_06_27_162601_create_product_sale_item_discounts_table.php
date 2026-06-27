<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_sale_item_discounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_sale_item_id')
                ->constrained('product_sale_items')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('discounted_price');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('show_special_box')->default(false);
            $table->string('special_box_title')->nullable();
            $table->text('special_box_description')->nullable();

            $table->timestamps();

            $table->unique('product_sale_item_id', 'psi_discount_unique');
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sale_item_discounts');
    }
};
