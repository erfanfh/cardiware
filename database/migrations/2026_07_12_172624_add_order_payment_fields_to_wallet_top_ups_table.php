<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_top_ups', function (Blueprint $table) {
            $table->string('purpose', 40)->default('manual')->after('status')->index();
            $table->foreignId('order_id')->nullable()->after('wallet_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('wallet_hold_id')->nullable()->unique()->after('order_id')->constrained('wallet_holds')->restrictOnDelete();
            $table->foreignId('order_payment_id')->nullable()->unique()->after('payment_id')->constrained('payments')->restrictOnDelete();
            $table->timestamp('failed_at')->nullable()->after('completed_at');
            $table->text('failure_reason')->nullable()->after('failed_at');

            $table->index(['purpose', 'status', 'created_at']);
            $table->index(['order_id', 'purpose', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_top_ups', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'purpose', 'status']);
            $table->dropIndex(['purpose', 'status', 'created_at']);

            $table->dropConstrainedForeignId('order_payment_id');
            $table->dropConstrainedForeignId('wallet_hold_id');
            $table->dropConstrainedForeignId('order_id');

            $table->dropIndex(['purpose']);

            $table->dropColumn(['purpose', 'failed_at', 'failure_reason']);
        });
    }
};
