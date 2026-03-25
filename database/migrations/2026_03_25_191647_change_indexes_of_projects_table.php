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
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['status']);

            $table->index(['status', 'deleted_at', 'created_at'], 'projects_status_deleted_created_index');
            $table->index(['status', 'deleted_at', 'progress_percent'], 'projects_status_deleted_progress_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('projects_status_deleted_created_index');
            $table->dropIndex('projects_status_deleted_progress_index');

            $table->index(['status']);
        });
    }
};
