<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->string('monitor_process_name', 255)
                ->nullable()
                ->after('inject_dll_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn('monitor_process_name');
        });
    }
};
