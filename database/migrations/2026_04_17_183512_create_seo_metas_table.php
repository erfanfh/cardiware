<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('focus_keyword')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('robots', 255)->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->foreignId('og_image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('x_title')->nullable();
            $table->text('x_description')->nullable();
            $table->foreignId('x_image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('schema_type', 100)->nullable();
            $table->json('schema_settings')->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
            $table->index('focus_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
