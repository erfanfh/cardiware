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
        Schema::create('purchase_access_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_access_id')->constrained('purchase_accesses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->foreignId('product_sale_item_file_id')->nullable()->constrained('product_sale_item_files')->nullOnDelete();
            $table->string('file_name')->nullable();
            $table->string('file_disk')->nullable();
            $table->string('file_path', 1000)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['purchase_access_id', 'created_at'], 'purchase_download_access_created_index');
            $table->index(['user_id', 'created_at']);
            $table->index(['product_sale_item_id', 'created_at'], 'purchase_download_sale_item_created_index');
            $table->index('product_sale_item_file_id', 'purchase_download_file_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_access_downloads');
    }
};
