<?php

namespace Tests\Feature\Promotion;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class CouponStackingMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
        });
        foreach (['carts', 'orders'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->nullable()->constrained();
                $table->string('coupon_code')->nullable();
                $table->unsignedBigInteger('coupon_discount_amount')->default(0);
            });
        }
        foreach (['coupon_reservations', 'coupon_redemptions'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->foreignId('order_id')->nullable()->constrained();
                $table->foreignId('coupon_id')->constrained();
                $table->unsignedBigInteger('discount_amount')->default(0);
                $table->unique('order_id');
                if ($name === 'coupon_redemptions') {
                    $table->unique(['coupon_id', 'order_id']);
                }
            });
        }

        DB::table('coupons')->insert([['id' => 1, 'code' => 'OLD'], ['id' => 2, 'code' => 'NEW']]);
        foreach (['carts', 'orders'] as $name) {
            DB::table($name)->insert(['id' => 1, 'coupon_id' => 1, 'coupon_code' => 'OLD', 'coupon_discount_amount' => 10000]);
        }
        foreach (['coupon_reservations', 'coupon_redemptions'] as $name) {
            DB::table($name)->insert(['order_id' => 1, 'coupon_id' => 1, 'discount_amount' => 10000]);
        }
    }

    public function test_migration_preserves_old_data_disables_stacking_by_default_and_allows_one_use_per_code_per_order(): void
    {
        $migration = require database_path('migrations/2026_10_12_120000_add_coupon_stacking.php');
        $migration->up();
        $this->assertSame(0, (int) DB::table('coupons')->where('id', 1)->value('is_stackable'));
        foreach (['carts', 'orders'] as $name) {
            $this->assertDatabaseHas($name, ['id' => 1, 'coupon_id' => 1, 'coupon_code' => 'OLD', 'coupon_discount_amount' => 10000]);
            $this->assertNull(DB::table($name)->where('id', 1)->value('applied_coupons'));
        }
        foreach (['coupon_reservations', 'coupon_redemptions'] as $name) {
            DB::table($name)->insert(['order_id' => 1, 'coupon_id' => 2, 'discount_amount' => 5000]);
            $this->assertSame(2, DB::table($name)->where('order_id', 1)->count());
            try {
                DB::table($name)->insert(['order_id' => 1, 'coupon_id' => 2, 'discount_amount' => 5000]);
                $this->fail('The same coupon must remain unique per order.');
            } catch (QueryException $exception) {
                $this->assertStringContainsString('UNIQUE', $exception->getMessage());
            }
        }
    }

    public function test_migration_can_be_rolled_back_and_reapplied_for_legacy_orders(): void
    {
        $migration = require database_path('migrations/2026_10_12_120000_add_coupon_stacking.php');
        $migration->up();
        $migration->down();
        $this->assertFalse(Schema::hasColumn('coupons', 'is_stackable'));
        $this->assertFalse(Schema::hasColumn('carts', 'applied_coupons'));
        $this->assertFalse(Schema::hasColumn('orders', 'applied_coupons'));
        foreach (['coupon_reservations', 'coupon_redemptions'] as $name) {
            $this->assertDatabaseHas($name, ['order_id' => 1, 'coupon_id' => 1, 'discount_amount' => 10000]);
            try {
                DB::table($name)->insert(['order_id' => 1, 'coupon_id' => 2]);
                $this->fail('Rollback must restore the original single-coupon constraint.');
            } catch (QueryException $exception) {
                $this->assertStringContainsString('UNIQUE', $exception->getMessage());
            }
        }
        $migration->up();
        $this->assertTrue(Schema::hasColumn('orders', 'applied_coupons'));
    }

    public function test_rollback_refuses_to_discard_multi_coupon_order_history(): void
    {
        $migration = require database_path('migrations/2026_10_12_120000_add_coupon_stacking.php');
        $migration->up();
        DB::table('coupon_redemptions')->insert(['order_id' => 1, 'coupon_id' => 2, 'discount_amount' => 5000]);
        try {
            $migration->down();
            $this->fail('Rollback must preserve multi-coupon history.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Cannot roll back coupon stacking', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasColumn('orders', 'applied_coupons'));
        $this->assertTrue(Schema::hasColumn('coupons', 'is_stackable'));
        $this->assertSame(2, DB::table('coupon_redemptions')->count());
    }
}
