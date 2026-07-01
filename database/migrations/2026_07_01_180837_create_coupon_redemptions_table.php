<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupon_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('code', 80);

            $table->unsignedBigInteger('order_subtotal')->default(0);
            $table->unsignedBigInteger('eligible_amount')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);

            $table->timestamp('redeemed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['coupon_id', 'user_id']);
            $table->index('code');
            $table->index('redeemed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_redemptions');
    }
};
