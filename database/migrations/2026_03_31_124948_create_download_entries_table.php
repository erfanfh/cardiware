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
        Schema::create('download_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_section_id')->constrained('download_sections')->cascadeOnDelete();
            $table->string('public_id', 50)->unique();
            $table->string('type', 50);
            $table->string('title');
            $table->text('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('download_click_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['download_section_id', 'sort_order']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_entries');
    }
};
