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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('status', 30)->default('active')->index();
            $table->unsignedBigInteger('balance')->default(0);
            $table->unsignedBigInteger('withdrawable_balance')->default(0);
            $table->unsignedBigInteger('held_balance')->default(0);
            $table->unsignedBigInteger('held_withdrawable_balance')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
