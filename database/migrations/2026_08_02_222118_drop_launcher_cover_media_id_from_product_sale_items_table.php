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
        Schema::table('product_sale_items', function (Blueprint $table) {
            $table->dropForeign(['launcher_cover_media_id']);
            $table->dropColumn(['launcher_cover_media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table) {
            $table->unsignedBigInteger('launcher_cover_media_id')->nullable()->after('launcher_excerpt');
            $table->foreign('launcher_cover_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });
    }
};
