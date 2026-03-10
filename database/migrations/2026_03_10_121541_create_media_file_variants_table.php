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
        Schema::create('media_file_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->string('variant')->default('original');
            $table->string('format');
            $table->string('path');
            $table->unsignedBigInteger('byte_size');
            $table->unsignedMediumInteger('width')->nullable();
            $table->unsignedMediumInteger('height')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['media_file_id', 'variant', 'format']);
            $table->unique(['disk', 'path']);
            $table->index('checksum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_file_variants');
    }
};
