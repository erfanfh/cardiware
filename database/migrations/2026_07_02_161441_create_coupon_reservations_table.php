<?php

use App\Domain\Promotion\Enums\CouponReservationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupon_reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('code');

            $table->unsignedBigInteger('eligible_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);

            $table->string('status')->default(CouponReservationStatusEnum::ACTIVE->value);

            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('expired_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->unique('order_id');

            $table->index(['coupon_id', 'status', 'expires_at']);
            $table->index(['user_id', 'coupon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_reservations');
    }
};
