<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launcher_releases', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 30)->default('windows');
            $table->string('channel', 30)->default('stable');
            $table->string('version', 50);
            $table->string('minimum_supported_version', 50)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('is_enabled')->default(false);
            $table->text('download_url');
            $table->string('file_name', 255);
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->text('release_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'channel', 'version']);
            $table->index(['platform', 'channel', 'is_enabled', 'published_at'], 'launcher_releases_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_releases');
    }
};
