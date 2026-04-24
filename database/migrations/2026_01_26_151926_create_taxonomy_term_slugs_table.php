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
        Schema::create('taxonomy_term_slugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_term_id')->constrained('taxonomy_terms')->cascadeOnDelete();
            $table->string('type');
            $table->string('route_key', 255);
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['type', 'route_key']);
            $table->index(['taxonomy_term_id', 'is_current']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomy_term_slugs');
    }
};
