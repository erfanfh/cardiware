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
        Schema::create('feature_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_definition_id')->constrained('feature_definitions');
            $table->morphs('subject');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['feature_definition_id', 'subject_type', 'subject_id'], 'feature_assignments_definition_id_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_assignments');
    }
};
