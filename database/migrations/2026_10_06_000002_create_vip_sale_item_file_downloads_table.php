<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vip_sale_item_file_downloads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vip_sale_item_claim_id')
                ->constrained('vip_sale_item_claims')->cascadeOnDelete();
            $table->foreignId('product_sale_item_file_id')
                ->constrained('product_sale_item_files')->cascadeOnDelete();
            $table->unsignedInteger('downloads_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();

            $table->unique(['vip_sale_item_claim_id', 'product_sale_item_file_id'], 'vip_claim_file_downloads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_sale_item_file_downloads');
    }
};
