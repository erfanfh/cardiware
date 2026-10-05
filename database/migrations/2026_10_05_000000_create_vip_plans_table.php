<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vip_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days');
            $table->unsignedBigInteger('price_toman');
            $table->boolean('is_active')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_plans');
    }
};
