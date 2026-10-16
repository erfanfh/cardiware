<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'ticket_messages'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->timestamp('customer_sms_sent_at')->nullable();
                $table->string('customer_sms_message_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['orders', 'ticket_messages'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropColumn(['customer_sms_sent_at', 'customer_sms_message_id']);
            });
        }
    }
};
