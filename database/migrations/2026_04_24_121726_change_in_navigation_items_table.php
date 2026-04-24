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
        Schema::table('navigation_items', function (Blueprint $table) {
            $table->dropColumn('mega_menu_style');
            $table->string('mega_menu_description')->nullable()->after('mega_menu_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('navigation_items', function (Blueprint $table) {
            $table->string('mega_menu_style', 50)->nullable()->default('default')->after('mega_menu_columns');
            $table->dropColumn('mega_menu_description');
        });
    }
};
