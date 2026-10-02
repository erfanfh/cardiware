<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedSmallInteger('seller_commission_basis_points')->default(0);
        });

        Schema::table('order_items', function (Blueprint $table): void {
            // Null identifies an order item created before commission snapshots existed.
            $table->unsignedSmallInteger('seller_commission_basis_points')->nullable();
            $table->unsignedSmallInteger('platform_commission_basis_points')->nullable();
            $table->unsignedBigInteger('seller_commission_amount')->nullable();
            $table->unsignedBigInteger('platform_commission_amount')->nullable();
            $table->timestamp('commission_snapshotted_at')->nullable();
        });

        // Orders paid before this feature remain 100% platform-owned, even if
        // their status is later changed and they are marked paid again.
        DB::table('order_items')
            ->whereIn('order_id', DB::table('orders')->whereNotNull('paid_at')->select('id'))
            ->update([
                'seller_commission_basis_points' => 0,
                'platform_commission_basis_points' => 10000,
                'seller_commission_amount' => 0,
                'platform_commission_amount' => DB::raw('final_total_amount'),
                'commission_snapshotted_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn([
                'seller_commission_basis_points',
                'platform_commission_basis_points',
                'seller_commission_amount',
                'platform_commission_amount',
                'commission_snapshotted_at',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('seller_commission_basis_points');
        });
    }
};
