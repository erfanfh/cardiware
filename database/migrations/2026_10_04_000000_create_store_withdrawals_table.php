<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('store_withdrawals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->uuid('request_key')->unique();
            $table->string('status', 20);
            $table->unsignedBigInteger('amount');
            $table->text('iban');
            $table->text('card_number');
            $table->text('account_holder');
            $table->text('seller_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('payment_reference', 160)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_withdrawals');
    }
};
