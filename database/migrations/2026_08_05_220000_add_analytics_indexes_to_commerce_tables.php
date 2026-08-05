<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index(
                ['status', 'paid_at'],
                'orders_status_paid_at_index',
            );

            $table->index(
                ['coupon_id', 'status', 'paid_at'],
                'orders_coupon_status_paid_at_index',
            );
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(
                ['gateway', 'status', 'paid_at'],
                'payments_gateway_status_paid_at_index',
            );

            $table->index(
                ['gateway', 'created_at'],
                'payments_gateway_created_at_index',
            );

            $table->index(
                ['payable_type', 'status', 'paid_at'],
                'payments_payable_status_paid_at_index',
            );
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->index(
                ['product_id', 'order_id'],
                'order_items_product_order_index',
            );

            $table->index(
                ['store_id', 'order_id'],
                'order_items_store_order_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropIndex('order_items_product_order_index');
            $table->dropIndex('order_items_store_order_index');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_gateway_status_paid_at_index');
            $table->dropIndex('payments_gateway_created_at_index');
            $table->dropIndex('payments_payable_status_paid_at_index');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_status_paid_at_index');
            $table->dropIndex('orders_coupon_status_paid_at_index');
        });
    }
};
