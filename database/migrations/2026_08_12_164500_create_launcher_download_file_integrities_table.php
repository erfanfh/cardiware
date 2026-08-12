<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('launcher_download_file_integrities', function (Blueprint $table): void {
            $table->id();
            $table->string('source_type', 20);
            $table->unsignedBigInteger('source_file_id');
            $table->char('source_url_hash', 64);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('mime_type')->nullable();
            $table->timestamp('verified_at');
            $table->timestamps();

            $table->unique(
                ['source_type', 'source_file_id'],
                'launcher_download_integrity_source_unique'
            );

            $table->index('source_url_hash');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_download_file_integrities');
    }
};
