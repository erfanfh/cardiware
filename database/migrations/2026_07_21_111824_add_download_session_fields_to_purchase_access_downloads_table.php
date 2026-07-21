<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'purchase_access_downloads',
            function (Blueprint $table): void {
                $table->uuid('download_session_id')->nullable()->after('product_sale_item_file_id');
                $table->timestamp('session_expires_at')->nullable()->after('download_session_id');
                $table->timestamp('last_prepared_at')->nullable()->after('session_expires_at');
                $table->unsignedInteger('prepare_count')->default(1)->after('last_prepared_at');

                $table->unique(
                    [
                        'purchase_access_id',
                        'product_sale_item_file_id',
                        'download_session_id',
                    ],
                    'purchase_download_file_session_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'purchase_access_downloads',
            function (Blueprint $table): void {
                $table->dropUnique(
                    'purchase_download_file_session_unique'
                );

                $table->dropColumn([
                    'download_session_id',
                    'session_expires_at',
                    'last_prepared_at',
                    'prepare_count',
                ]);
            }
        );
    }
};
