<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_winners', function (Blueprint $table): void {
            $table->timestamp('winner_reminder_sms_failed_at')->nullable()->after('winner_reminder_sms_message_id');
            $table->text('winner_reminder_sms_failure')->nullable()->after('winner_reminder_sms_failed_at');
        });
    }

    public function down(): void
    {
        Schema::table('draw_winners', function (Blueprint $table): void {
            $table->dropColumn([
                'winner_reminder_sms_failed_at',
                'winner_reminder_sms_failure',
            ]);
        });
    }
};
