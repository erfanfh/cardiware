<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index('created_at', 'users_created_at_report_index');
        });

        Schema::table('otps', function (Blueprint $table): void {
            $table->index(['key_type', 'created_at'], 'otps_key_type_created_at_report_index');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->index('created_at', 'comments_created_at_report_index');
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->index('created_at', 'tickets_created_at_report_index');
        });

        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->index('created_at', 'purchase_downloads_created_at_report_index');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->dropIndex('purchase_downloads_created_at_report_index');
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex('tickets_created_at_report_index');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex('comments_created_at_report_index');
        });

        Schema::table('otps', function (Blueprint $table): void {
            $table->dropIndex('otps_key_type_created_at_report_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_created_at_report_index');
        });
    }
};
