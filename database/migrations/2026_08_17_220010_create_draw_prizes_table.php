<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draw_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('draw_campaigns')
                ->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('delivery_description')->nullable();
            $table->unsignedBigInteger('estimated_value')->nullable();
            $table->foreignId('media_file_id')
                ->nullable()
                ->constrained('media_files')
                ->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedSmallInteger('reserve_winners_count')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['campaign_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draw_prizes');
    }
};
