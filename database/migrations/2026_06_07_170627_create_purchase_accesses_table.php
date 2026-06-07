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
        Schema::create('purchase_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->string('status', 30);
            $table->unsignedInteger('download_limit')->nullable();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_item_id');

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'product_sale_item_id', 'status'], 'purchase_access_user_sale_item_status_index');
            $table->index(['product_id', 'status']);
            $table->index(['product_sale_item_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_accesses');
    }
};
