<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('draw_winners', function (Blueprint $table) {
            $table->timestamp('confirmation_deadline_at')->nullable()->after('selected_at');
            $table->timestamp('expired_at')->nullable()->after('rejection_reason');
            $table->unsignedSmallInteger('promoted_for_position')->nullable()->after('promoted_at');

            $table->index(['campaign_id', 'status', 'confirmation_deadline_at'], 'draw_winners_campaign_status_deadline_idx');
            $table->index(['prize_id', 'promoted_for_position', 'promoted_at'], 'draw_winners_prize_promoted_position_idx');
        });
    }

    public function down(): void
    {
        Schema::table('draw_winners', function (Blueprint $table) {
            $table->dropIndex('draw_winners_campaign_status_deadline_idx');
            $table->dropIndex('draw_winners_prize_promoted_position_idx');
            $table->dropColumn(['confirmation_deadline_at', 'expired_at', 'promoted_for_position']);
        });
    }
};
