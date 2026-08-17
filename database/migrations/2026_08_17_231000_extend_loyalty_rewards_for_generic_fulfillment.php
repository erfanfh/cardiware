<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_offers', function (Blueprint $table) {
            $table->nullableMorphs('target');
        });

        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->string('status', 30)->default('completed')->index();
            $table->nullableMorphs('fulfillment');
            $table->foreignId('reversal_point_ledger_entry_id')
                ->nullable()
                ->constrained('point_ledger_entries')
                ->restrictOnDelete();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('reversed_at')->nullable();
        });

        DB::table('reward_redemptions')
            ->whereNull('fulfilled_at')
            ->update([
                'status' => 'completed',
                'fulfilled_at' => DB::raw('created_at'),
            ]);


        DB::table('reward_redemptions')
            ->whereNotNull('coupon_id')
            ->whereNull('fulfillment_type')
            ->update([
                'fulfillment_type' => \App\Domain\Promotion\Models\Coupon::class,
                'fulfillment_id' => DB::raw('coupon_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropForeign(['reversal_point_ledger_entry_id']);
            $table->dropColumn([
                'status',
                'reversal_point_ledger_entry_id',
                'fulfilled_at',
                'reversed_at',
            ]);
            $table->dropMorphs('fulfillment');
        });

        Schema::table('reward_offers', function (Blueprint $table) {
            $table->dropMorphs('target');
        });
    }
};
