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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores');
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('type', 30);
            $table->string('structure', 30);
            $table->string('status', 40)->index();
            $table->foreignId('picture_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->timestamp('published_at')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['store_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
