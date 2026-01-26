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
        Schema::create('taxonomables', function (Blueprint $table) {
            $table->foreignId('taxonomy_term_id')->constrained()->cascadeOnDelete();
            $table->string('taxonomable_type');
            $table->unsignedBigInteger('taxonomable_id');
            $table->timestamps();

            $table->primary(['taxonomy_term_id', 'taxonomable_type', 'taxonomable_id']);
            $table->index(['taxonomable_type', 'taxonomable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxonomables');
    }
};
