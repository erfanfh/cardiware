<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * برترین فروشنده‌های هفته/ماه/سال و جوایزشان (افزایش سهم فروشنده = کمیسیون کمتر،
 * کد تخفیف، نمایش در صفحه اصلی)، و جایگاه «فروشگاه ویژه» صفحه اصلی که هم خودکار
 * (جایزه) و هم دستی (مدیر) پر می‌شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_period_closures', function (Blueprint $table): void {
            $table->id();
            $table->string('period_type', 10);
            $table->string('period_key', 20);
            $table->string('period_label', 80);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('closed_at');
            $table->unsignedTinyInteger('winners_count')->default(0);
            $table->timestamps();
            $table->unique(['period_type', 'period_key']);
        });

        Schema::create('store_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('closure_id')->constrained('seller_period_closures')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('period_type', 10);
            $table->string('period_key', 20);
            $table->string('period_label', 80);
            $table->unsignedTinyInteger('rank');
            $table->unsignedBigInteger('net_sales');
            $table->unsignedInteger('orders_count')->default(0);
            // سهم اضافه‌ی فروشنده (واحد: صدم درصد؛ ۲۰۰ = ۲٪) در بازه‌ی جایزه.
            $table->unsignedSmallInteger('commission_bonus_bp')->default(0);
            $table->timestamp('bonus_starts_at')->nullable();
            $table->timestamp('bonus_ends_at')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->string('coupon_code', 60)->nullable();
            $table->timestamps();
            $table->unique(['period_type', 'period_key', 'rank']);
            $table->index(['store_id', 'bonus_ends_at']);
        });

        Schema::create('store_spotlights', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('store_reward_id')->nullable()->constrained('store_rewards')->nullOnDelete();
            $table->string('source', 20)->default('admin');
            $table->string('headline', 120)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['starts_at', 'ends_at']);
        });

        // جایگاه «فروشگاه ویژه» در صفحه اصلی، کنار باکس گالری (اگر چیدمان خانه وجود دارد).
        if (Schema::hasTable('page_layout_elements')) {
            $anchor = DB::table('page_layout_elements')->where('key', 'gallery-cta')->first();
            if ($anchor && ! DB::table('page_layout_elements')->where('page_layout_area_id', $anchor->page_layout_area_id)->where('key', 'featured-store')->exists()) {
                DB::table('page_layout_elements')->insert([
                    'page_layout_area_id' => $anchor->page_layout_area_id,
                    'key' => 'featured-store',
                    'type' => 'featured_store',
                    'title' => 'فروشگاه ویژه',
                    'description' => 'محصولات برترین فروشنده یا فروشگاه انتخابی مدیر',
                    'is_active' => true,
                    'sort_order' => (int) $anchor->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_layout_elements')) {
            DB::table('page_layout_elements')->where('key', 'featured-store')->delete();
        }
        Schema::dropIfExists('store_spotlights');
        Schema::dropIfExists('store_rewards');
        Schema::dropIfExists('seller_period_closures');
    }
};
