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
        Schema::create('content_slugs', function (Blueprint $table) {
            $table->id();
            $table->morphs('sluggable');
            $table->string('slug')->unique();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(
                ['sluggable_type', 'sluggable_id', 'is_current'],
                'content_slugs_sluggable_current_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_slugs');
    }
};
