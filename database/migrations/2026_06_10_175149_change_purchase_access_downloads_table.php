<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table) {
            $table->index(
                ['purchase_access_id', 'product_sale_item_file_id', 'created_at'],
                'purchase_download_access_file_created_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table) {
            $table->dropIndex('purchase_download_access_file_created_index');
        });
    }
};
