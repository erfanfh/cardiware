<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('point_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();

            // Signed because reversing already-spent earned points may create debt.
            $table->bigInteger('balance')->default(0);
            $table->unsignedBigInteger('lifetime_earned')->default(0);
            $table->unsignedBigInteger('lifetime_spent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_accounts');
    }
};
