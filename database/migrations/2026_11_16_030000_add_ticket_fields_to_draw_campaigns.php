<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فیلدهای بلیط قرعه‌کشی:
 * - points_per_ticket: امتیاز لازم برای هر کد قرعه‌کشی
 * - ticket_price: قیمت نقدی هر بلیط (تومان، اختیاری)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_campaigns', function (Blueprint $table) {
            $table->unsignedInteger('points_per_ticket')->default(100)->after('metadata');
            $table->unsignedBigInteger('ticket_price')->nullable()->after('points_per_ticket');
        });
    }

    public function down(): void
    {
        Schema::table('draw_campaigns', fn (Blueprint $table) => $table->dropColumn(['points_per_ticket', 'ticket_price']));
    }
};
