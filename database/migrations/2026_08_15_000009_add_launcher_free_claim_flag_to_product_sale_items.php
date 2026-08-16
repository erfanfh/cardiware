<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->boolean('launcher_free_claim_enabled')
                ->default(false)
                ->after('launcher_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->dropColumn('launcher_free_claim_enabled');
        });
    }
};
