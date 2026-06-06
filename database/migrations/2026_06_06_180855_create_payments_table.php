<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->string('gateway', 60);
            $table->string('status', 60);
            $table->unsignedBigInteger('amount');
            $table->string('tracking_code')->nullable()->unique();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->string('gateway_status')->nullable();
            $table->text('gateway_message')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('callback_payload')->nullable();
            $table->json('verify_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id', 'status']);
            $table->index(['gateway', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
