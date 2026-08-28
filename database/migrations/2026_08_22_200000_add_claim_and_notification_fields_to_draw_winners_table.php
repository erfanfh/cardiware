<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draw_winners', function (Blueprint $table): void {
            $table->timestamp('claimed_at')->nullable()->after('confirmed_by_user_id');
            $table->string('claim_contact_name')->nullable()->after('claimed_at');
            $table->string('claim_mobile', 20)->nullable()->after('claim_contact_name');
            $table->string('claim_postal_code', 20)->nullable()->after('claim_mobile');
            $table->text('claim_address')->nullable()->after('claim_postal_code');
            $table->string('claim_ip', 45)->nullable()->after('claim_address');

            $table->timestamp('winner_sms_sent_at')->nullable()->after('claim_ip');
            $table->string('winner_sms_message_id')->nullable()->after('winner_sms_sent_at');
            $table->timestamp('winner_sms_failed_at')->nullable()->after('winner_sms_message_id');
            $table->text('winner_sms_failure')->nullable()->after('winner_sms_failed_at');
            $table->timestamp('winner_reminder_sms_sent_at')->nullable()->after('winner_sms_failure');
            $table->string('winner_reminder_sms_message_id')->nullable()->after('winner_reminder_sms_sent_at');

            $table->index(['status', 'confirmation_deadline_at', 'winner_reminder_sms_sent_at'], 'draw_winners_reminder_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::table('draw_winners', function (Blueprint $table): void {
            $table->dropIndex('draw_winners_reminder_lookup_idx');
            $table->dropColumn([
                'claimed_at',
                'claim_contact_name',
                'claim_mobile',
                'claim_postal_code',
                'claim_address',
                'claim_ip',
                'winner_sms_sent_at',
                'winner_sms_message_id',
                'winner_sms_failed_at',
                'winner_sms_failure',
                'winner_reminder_sms_sent_at',
                'winner_reminder_sms_message_id',
            ]);
        });
    }
};
