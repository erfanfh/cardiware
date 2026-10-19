<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('download_banner_media_id')->nullable()
                ->constrained('media_files')->nullOnDelete();
            $table->string('download_banner_url', 2048)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['download_banner_media_id']);
            $table->dropColumn(['download_banner_media_id', 'download_banner_url']);
        });
    }
};
