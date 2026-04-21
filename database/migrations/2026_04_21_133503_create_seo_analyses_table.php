<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seo_analyses', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->unsignedTinyInteger('score')->default(0);
            $table->unsignedTinyInteger('max_score')->default(100);
            $table->unsignedSmallInteger('problems_count')->default(0);
            $table->unsignedSmallInteger('improvements_count')->default(0);
            $table->unsignedSmallInteger('good_count')->default(0);
            $table->json('results');
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['score']);
            $table->index(['analyzed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_analyses');
    }
};
