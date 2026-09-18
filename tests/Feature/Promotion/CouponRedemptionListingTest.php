<?php

namespace Tests\Feature\Promotion;

use App\Application\Promotion\Listings\CouponRedemptionListing;
use App\Domain\Promotion\Models\CouponRedemption;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CouponRedemptionListingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('code');
            $table->unsignedBigInteger('order_subtotal')->default(0);
            $table->unsignedBigInteger('eligible_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->timestamp('redeemed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'ali',
                'first_name' => 'علی',
                'last_name' => 'احمدی',
                'display_name' => 'علی احمدی',
                'mobile' => '09120000001',
                'email' => 'ali@example.com',
            ],
            [
                'id' => 2,
                'username' => 'sara',
                'first_name' => 'سارا',
                'last_name' => 'محمدی',
                'display_name' => 'سارا محمدی',
                'mobile' => '09120000002',
                'email' => 'sara@example.com',
            ],
        ]);

        DB::table('orders')->insert([
            ['id' => 10, 'order_number' => 'MW-100'],
            ['id' => 20, 'order_number' => 'MW-200'],
        ]);

        DB::table('coupon_redemptions')->insert([
            [
                'id' => 100,
                'coupon_id' => 5,
                'user_id' => 1,
                'order_id' => 10,
                'code' => 'SUMMER',
                'order_subtotal' => 500_000,
                'eligible_amount' => 500_000,
                'discount_amount' => 50_000,
                'redeemed_at' => '2026-08-01 10:00:00',
            ],
            [
                'id' => 200,
                'coupon_id' => 5,
                'user_id' => 2,
                'order_id' => 20,
                'code' => 'SUMMER',
                'order_subtotal' => 800_000,
                'eligible_amount' => 700_000,
                'discount_amount' => 80_000,
                'redeemed_at' => '2026-08-02 10:00:00',
            ],
        ]);
    }

    public function test_it_filters_redemptions_by_user_and_order(): void
    {
        $listing = app(CouponRedemptionListing::class);

        $byUser = $listing
            ->handle(CouponRedemption::query(), ['user' => '09120000001'])
            ->pluck('id')
            ->all();

        $byOrder = app(CouponRedemptionListing::class)
            ->handle(CouponRedemption::query(), ['order_number' => 'MW-200'])
            ->pluck('id')
            ->all();

        self::assertSame([100], $byUser);
        self::assertSame([200], $byOrder);
    }

    public function test_it_filters_amount_range_and_sorts_redemptions(): void
    {
        $ids = app(CouponRedemptionListing::class)
            ->handle(CouponRedemption::query(), [
                'discount_from' => 40_000,
                'discount_to' => 90_000,
                'sort' => 'discount_amount',
                'direction' => 'desc',
            ])
            ->pluck('id')
            ->all();

        self::assertSame([200, 100], $ids);
    }

    public function test_redemption_table_renders_user_order_and_financial_details(): void
    {
        $redemptions = CouponRedemption::query()
            ->with(['user', 'order'])
            ->paginate(10);

        $html = view(
            'admin.promotion.coupons.redemptions.partials.list-table',
            compact('redemptions'),
        )->render();

        self::assertStringContainsString('علی احمدی', $html);
        self::assertStringContainsString('MW-100', $html);
        self::assertStringContainsString('50,000', $html);
    }
}
