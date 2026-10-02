<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_addons', function (Blueprint $table): void {
            // Null keeps the existing behavior: use the parent product's rate.
            $table->unsignedSmallInteger('seller_commission_basis_points')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_addons', function (Blueprint $table): void {
            $table->dropColumn('seller_commission_basis_points');
        });
    }
};
