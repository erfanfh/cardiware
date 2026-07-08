<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupon_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->morphs('target');
            $table->timestamps();

            $table->unique(['coupon_id', 'target_type', 'target_id'], 'coupon_targets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_targets');
    }
};
