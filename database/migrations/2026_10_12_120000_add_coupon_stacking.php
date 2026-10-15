<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_stackable')->default(false);
        });

        foreach (['carts', 'orders'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->json('applied_coupons')->nullable();
            });
        }

        Schema::table('coupon_reservations', function (Blueprint $table) {
            // Keep an order index for the foreign key when removing the old unique index.
            $table->index('order_id', 'coupon_reservations_order_id_index');
            $table->unique(['order_id', 'coupon_id']);
        });
        Schema::table('coupon_reservations', function (Blueprint $table) {
            $table->dropUnique(['order_id']);
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->index('order_id', 'coupon_redemptions_order_id_index');
        });
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            // The existing (coupon_id, order_id) unique index still prevents duplicate usage.
            $table->dropUnique(['order_id']);
        });
    }

    public function down(): void
    {
        foreach (['coupon_reservations', 'coupon_redemptions'] as $name) {
            if (DB::table($name)->whereNotNull('order_id')
                ->groupBy('order_id')->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Cannot roll back coupon stacking while orders have multiple coupon reservations or redemptions.');
            }
        }

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->unique('order_id');
        });
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropIndex('coupon_redemptions_order_id_index');
        });

        Schema::table('coupon_reservations', function (Blueprint $table) {
            $table->unique('order_id');
        });
        Schema::table('coupon_reservations', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'coupon_id']);
            $table->dropIndex('coupon_reservations_order_id_index');
        });

        foreach (['carts', 'orders'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('applied_coupons');
            });
        }
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('is_stackable');
        });
    }
};
