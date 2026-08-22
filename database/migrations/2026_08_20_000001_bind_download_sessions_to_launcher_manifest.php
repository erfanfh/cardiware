<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->unsignedBigInteger('manifest_revision')->nullable()->after('download_session_id');
            $table->char('file_sha256', 64)->nullable()->after('manifest_revision');
            $table->unsignedBigInteger('file_size_bytes')->nullable()->after('file_sha256');
            $table->timestamp('invalidated_at')->nullable()->after('session_expires_at')->index();
        });

        Schema::table('launcher_download_sessions', function (Blueprint $table): void {
            $table->unsignedBigInteger('manifest_revision')->nullable()->after('download_session_id');
            $table->char('file_sha256', 64)->nullable()->after('manifest_revision');
            $table->unsignedBigInteger('file_size_bytes')->nullable()->after('file_sha256');
            $table->string('file_name')->nullable()->after('file_size_bytes');
            $table->timestamp('invalidated_at')->nullable()->after('session_expires_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->dropColumn(['manifest_revision', 'file_sha256', 'file_size_bytes', 'invalidated_at']);
        });

        Schema::table('launcher_download_sessions', function (Blueprint $table): void {
            $table->dropColumn(['manifest_revision', 'file_sha256', 'file_size_bytes', 'file_name', 'invalidated_at']);
        });
    }
};
