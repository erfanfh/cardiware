<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            // free (پیش‌فرض رفتار فعلی) | purchase (خرید تکی) | vip (اشتراک ویژه)
            $table->string('access_type', 20)->default('free')->after('is_required');
            $table->unsignedBigInteger('price_toman')->nullable()->after('access_type');
            // subscription: فقط تا وقتی اشتراک VIP فعال است (تمدید ماهانه) | permanent: دائمی
            $table->string('vip_unlock_mode', 20)->default('subscription')->after('price_toman');
        });

        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->index(['product_sale_item_id', 'access_type']);
        });

        Schema::create('launcher_mod_grants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('launcher_mod_id');
            // purchase: خرید تکی (دائمی) | vip_permanent: بازکردن دائمی با اشتراک ویژه
            $table->string('source', 20);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'launcher_mod_id']);
            $table->index(['launcher_mod_id', 'source']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('launcher_mod_id')->references('id')->on('launcher_mods')->cascadeOnDelete();
        });

        Schema::table('launcher_checkout_sessions', function (Blueprint $table): void {
            $table->unsignedBigInteger('launcher_mod_id')->nullable()->after('product_sale_item_id');
            $table->foreign('launcher_mod_id')->references('id')->on('launcher_mods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('launcher_checkout_sessions', function (Blueprint $table): void {
            $table->dropForeign(['launcher_mod_id']);
            $table->dropColumn('launcher_mod_id');
        });

        Schema::dropIfExists('launcher_mod_grants');

        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->dropIndex(['product_sale_item_id', 'access_type']);
            $table->dropColumn(['access_type', 'price_toman', 'vip_unlock_mode']);
        });
    }
};
