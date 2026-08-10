<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->json('cpu_compatibility')
                ->nullable()
                ->after('run_sider_as_administrator');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn('cpu_compatibility');
        });
    }
};
