<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')
                ->nullable()
                ->after('cart_id')
                ->constrained('coupons')
                ->nullOnDelete();

            $table->string('coupon_code', 80)
                ->nullable()
                ->after('coupon_id');

            $table->unsignedBigInteger('coupon_eligible_amount')
                ->default(0)
                ->after('coupon_code');

            $table->unsignedBigInteger('coupon_discount_amount')
                ->default(0)
                ->after('coupon_eligible_amount');

            $table->index('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['coupon_code']);

            $table->dropConstrainedForeignId('coupon_id');

            $table->dropColumn([
                'coupon_code',
                'coupon_eligible_amount',
                'coupon_discount_amount',
            ]);
        });
    }
};
