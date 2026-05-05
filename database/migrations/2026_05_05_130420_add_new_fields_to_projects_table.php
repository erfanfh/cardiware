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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('product_url')->nullable()->after('body');
            $table->string('product_box_title')->nullable()->after('product_url');
            $table->string('product_box_subtitle')->nullable()->after('product_box_title');
            $table->string('product_box_button_text')->nullable()->after('product_box_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('product_url');
            $table->dropColumn('product_box_title');
            $table->dropColumn('product_box_subtitle');
            $table->dropColumn('product_box_button_text');
        });
    }
};
