<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_positions', function (Blueprint $table): void {
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['closes_at']);
            $table->dropColumn(['sort_order', 'closes_at']);
        });
    }

    public function down(): void
    {
        Schema::table('career_positions', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('closes_at')->nullable()->index();
        });
    }
};
