<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('coupon_discount_amount')
                ->default(0)
                ->after('discount_amount');

            $table
                ->unsignedBigInteger('final_total_amount')
                ->default(0)
                ->after('total_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table
                ->unsignedBigInteger('coupon_discount_amount')
                ->default(0)
                ->after('discount_amount');

            $table
                ->unsignedBigInteger('final_total_amount')
                ->default(0)
                ->after('total_amount');
        });

        DB::table('cart_items')->update([
            'final_total_amount' => DB::raw('total_amount'),
        ]);

        DB::table('order_items')->update([
            'final_total_amount' => DB::raw('total_amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn([
                'coupon_discount_amount',
                'final_total_amount',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'coupon_discount_amount',
                'final_total_amount',
            ]);
        });
    }
};
