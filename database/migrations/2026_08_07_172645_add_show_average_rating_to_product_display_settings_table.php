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
        Schema::table('product_display_settings', function (Blueprint $table) {
            $table->boolean('show_average_rating')->default(false)->after('show_sales_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_display_settings', function (Blueprint $table) {
            $table->dropColumn('show_average_rating');
        });
    }
};
