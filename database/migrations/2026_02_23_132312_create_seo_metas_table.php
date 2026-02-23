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
        Schema::create('seo_metas', function (Blueprint $table) {
            $table->id();
            $table->string('seoable_type');
            $table->unsignedBigInteger('seoable_id');
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots')->nullable();
            $table->boolean('nosnippet')->default(false);
            $table->smallInteger('max_snippet')->nullable();
            $table->enum('max_image_preview', ['none', 'standard', 'large'])->nullable();
            $table->smallInteger('max_video_preview')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_image_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->tinyInteger('seo_score')->nullable();
            $table->tinyInteger('readability_score')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();

            $table->timestamps();

            $table->index(['seoable_type', 'seoable_id']);
            $table->unique(['seoable_type', 'seoable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metas');
    }
};
