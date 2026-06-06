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
        Schema::table('product_addons', function (Blueprint $table) {
            $table->dropColumn('max_quantity_per_order');
            $table->dropColumn('requires_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_addons', function (Blueprint $table) {
            $table->unsignedInteger('max_quantity_per_order')->default(1)->after('price');
            $table->boolean('requires_purchase')->default(true)->after('max_quantity_per_order');
        });
    }
};
