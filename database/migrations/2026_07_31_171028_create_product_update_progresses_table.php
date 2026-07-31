<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_update_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->boolean('is_visible')->default(false)->index();
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            $table->date('started_at')->nullable();
            $table->date('release_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_update_progresses');
    }
};
