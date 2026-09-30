<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->restrictOnDelete();
            $table->string('method', 40);
            $table->unsignedBigInteger('amount');
            $table->foreignId('wallet_transaction_id')->nullable()->unique()->constrained('wallet_transactions')->restrictOnDelete();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('manual_reference')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_refunds');
    }
};
