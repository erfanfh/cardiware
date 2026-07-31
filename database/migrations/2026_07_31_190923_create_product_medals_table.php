<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_medals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('picture_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_medals');
    }
};
