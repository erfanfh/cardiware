<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('launcher_cover_media_id')
                ->nullable()
                ->after('launcher_excerpt');

            $table->foreign('launcher_cover_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->dropForeign(['launcher_cover_media_id']);
            $table->dropColumn('launcher_cover_media_id');
        });
    }
};
