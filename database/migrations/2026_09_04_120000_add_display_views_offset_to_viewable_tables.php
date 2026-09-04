<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'projects', 'videos', 'posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedInteger('display_views_offset')->default(0);
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'projects', 'videos', 'posts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('display_views_offset');
            });
        }
    }
};
