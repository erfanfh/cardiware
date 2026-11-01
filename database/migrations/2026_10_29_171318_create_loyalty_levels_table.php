<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_levels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('badge', 16)->nullable();
            $table->unsignedBigInteger('min_xp')->default(0);
            $table->unsignedInteger('reward_points')->default(0);
            $table->decimal('purchase_multiplier', 4, 2)->default(1);
            $table->string('color_key', 32)->default('brand');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_levels');
    }
};
