<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('visitor_hash', 64);
            $table->char('ip_hash', 64);
            $table->date('viewed_on');
            $table->timestamp('viewed_at');
            $table->unique(['poll_id', 'visitor_hash', 'viewed_on']);
            $table->index(['poll_id', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_views');
    }
};
