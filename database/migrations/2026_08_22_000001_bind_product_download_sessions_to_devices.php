<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->foreignId('launcher_device_id')
                ->nullable()
                ->after('user_id')
                ->constrained('launcher_devices')
                ->nullOnDelete();
            $table->index(
                ['purchase_access_id', 'launcher_device_id', 'download_session_id'],
                'purchase_download_device_session_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('purchase_access_downloads', function (Blueprint $table): void {
            $table->dropIndex('purchase_download_device_session_index');
            $table->dropConstrainedForeignId('launcher_device_id');
        });
    }
};
