<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launcher_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->string('description', 500)->nullable();
            $table->string('button_text', 80)->nullable();
            $table->foreignId('image_media_id')->constrained('media_files')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('target_type', 20)->default('product');
            $table->foreignId('target_sale_item_id')->nullable()
                ->constrained('product_sale_items')->nullOnDelete();
            $table->text('target_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['is_enabled', 'starts_at', 'ends_at', 'sort_order'], 'launcher_slides_active_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_slides');
    }
};
