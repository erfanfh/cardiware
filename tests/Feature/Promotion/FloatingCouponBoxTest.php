<?php

namespace Tests\Feature\Promotion;

use App\Application\Promotion\Actions\Coupon\EnsureSingleFloatingCouponAction;
use App\Domain\Promotion\Enums\CouponScopeEnum;
use App\Domain\Promotion\Enums\CouponStatusEnum;
use App\Domain\Promotion\Enums\CouponTypeEnum;
use App\Domain\Promotion\Models\Coupon;
use App\Livewire\Front\Promotion\FloatingCouponBox;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FloatingCouponBoxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->unsignedBigInteger('value');
            $table->unsignedBigInteger('max_discount_amount')->nullable();
            $table->string('scope');
            $table->string('status');
            $table->boolean('applies_to_all_users')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('minimum_order_amount')->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_floating_box_enabled')->default(false);
            $table->boolean('applies_to_addons')->default(false);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('coupon_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->timestamps();
        });
    }

    public function test_enabled_coupon_is_rendered_with_its_live_remaining_capacity(): void
    {
        $coupon = $this->createCoupon([
            'title' => 'تخفیف ویژه محصول تست',
            'usage_limit' => 20,
            'used_count' => 7,
            'is_floating_box_enabled' => true,
        ]);

        Livewire::test(FloatingCouponBox::class)
            ->assertSee('تخفیف ویژه محصول تست')
            ->assertSee('فقط 13 فرصت از 20 فرصت باقی مانده')
            ->assertSee($coupon->code);
    }

    public function test_coupon_box_switches_to_finished_message_when_capacity_reaches_zero(): void
    {
        $this->createCoupon([
            'title' => 'پیشنهاد تابستانه',
            'usage_limit' => 2,
            'used_count' => 2,
            'is_floating_box_enabled' => true,
        ]);

        Livewire::test(FloatingCouponBox::class)
            ->assertSee('تخفیف «پیشنهاد تابستانه» تمام شد')
            ->assertSee('ظرفیت تکمیل شد')
            ->assertDontSee('خرید با تخفیف');
    }

    public function test_disabled_coupon_does_not_render_the_floating_box(): void
    {
        $this->createCoupon([
            'title' => 'پیشنهاد مخفی',
            'is_floating_box_enabled' => false,
        ]);

        Livewire::test(FloatingCouponBox::class)
            ->assertDontSee('پیشنهاد مخفی');
    }

    public function test_enabling_a_coupon_disables_the_previous_floating_coupon(): void
    {
        $first = $this->createCoupon(['is_floating_box_enabled' => true]);
        $second = $this->createCoupon([
            'code' => 'SECOND-20',
            'is_floating_box_enabled' => true,
        ]);

        app(EnsureSingleFloatingCouponAction::class)->execute($second);

        $this->assertFalse($first->fresh()->is_floating_box_enabled);
        $this->assertTrue($second->fresh()->is_floating_box_enabled);
        $this->assertSame(1, Coupon::query()->where('is_floating_box_enabled', true)->count());
    }

    private function createCoupon(array $attributes = []): Coupon
    {
        return Coupon::query()->create(array_merge([
            'code' => 'SPECIAL-20',
            'title' => 'پیشنهاد ویژه',
            'type' => CouponTypeEnum::PERCENTAGE,
            'value' => 20,
            'scope' => CouponScopeEnum::ORDER,
            'status' => CouponStatusEnum::ACTIVE,
            'applies_to_all_users' => true,
            'minimum_order_amount' => 0,
            'usage_limit' => 20,
            'usage_limit_per_user' => 1,
            'used_count' => 0,
            'is_floating_box_enabled' => false,
            'applies_to_addons' => false,
        ], $attributes));
    }
}
