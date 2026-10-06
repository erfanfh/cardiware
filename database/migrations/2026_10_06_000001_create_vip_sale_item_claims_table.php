<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vip_sale_item_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_sale_item_id')->nullable()
                ->constrained('product_sale_items')->nullOnDelete();
            $table->string('product_title');
            $table->string('sale_item_title')->nullable();
            $table->timestamp('claimed_at');
            $table->timestamps();

            $table->unique(['user_id', 'product_sale_item_id']);
            $table->index(['user_id', 'claimed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_sale_item_claims');
    }
};
