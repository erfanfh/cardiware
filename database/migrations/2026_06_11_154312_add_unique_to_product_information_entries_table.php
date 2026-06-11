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
        Schema::table('product_information_entries', function (Blueprint $table) {
            $table->unique(['product_id', 'product_information_field_id'], 'product_info_entries_product_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_information_entries', function (Blueprint $table) {
            $table->dropUnique('product_info_entries_product_field_unique');
        });
    }
};
