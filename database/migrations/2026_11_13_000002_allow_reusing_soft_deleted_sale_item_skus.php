<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->tinyInteger('active_unique_key')
                ->virtualAs('CASE WHEN deleted_at IS NULL THEN 1 ELSE NULL END');
            $table->unique(['sku', 'active_unique_key'], 'product_sale_items_active_sku_unique');
            $table->dropUnique(['sku']);
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->unique('sku');
            $table->dropUnique('product_sale_items_active_sku_unique');
            $table->dropColumn('active_unique_key');
        });
    }
};
