<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->unsignedBigInteger('taxable_amount')->default(0)->after('discount_amount');
            $table->unsignedInteger('tax_rate_basis_points')->default(0)->after('taxable_amount');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('tax_rate_basis_points');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('taxable_amount')->default(0)->after('discount_amount');
            $table->unsignedInteger('tax_rate_basis_points')->default(0)->after('taxable_amount');
            $table->unsignedBigInteger('tax_amount')->default(0)->after('tax_rate_basis_points');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_amount')->default(0)->after('amount');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('tax_amount')->default(0)->after('amount');
        });

        // All pre-existing records were created before taxation existed.
        DB::table('carts')->update(['taxable_amount' => DB::raw('total_amount')]);
        DB::table('orders')->update(['taxable_amount' => DB::raw('total_amount')]);
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', fn (Blueprint $table) => $table->dropColumn('tax_amount'));
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn('tax_amount'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn([
            'taxable_amount', 'tax_rate_basis_points', 'tax_amount',
        ]));
        Schema::table('carts', fn (Blueprint $table) => $table->dropColumn([
            'taxable_amount', 'tax_rate_basis_points', 'tax_amount',
        ]));
    }
};
