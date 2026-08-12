<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->boolean('launcher_support_enabled')
                ->default(false)
                ->after('launcher_sort_order');

            $table->string('launcher_support_url', 2048)
                ->nullable()
                ->after('launcher_support_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->dropColumn([
                'launcher_support_enabled',
                'launcher_support_url',
            ]);
        });
    }
};
