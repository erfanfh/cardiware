<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('earning_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('title');
            $table->string('type', 50)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('priority')->default(100);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('config');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index(['type', 'is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earning_rules');
    }
};
