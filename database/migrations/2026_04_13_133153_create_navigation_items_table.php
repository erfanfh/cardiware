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
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('navigation_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->nullOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->string('url', 2048);
            $table->string('target', 20)->default('_self');
            $table->nullableMorphs('reference');
            $table->string('icon')->nullable();
            $table->string('badge')->nullable();
            $table->string('css_class')->nullable();
            $table->boolean('is_mega_menu')->default(false);
            $table->string('mega_menu_title')->nullable();
            $table->unsignedTinyInteger('mega_menu_columns')->default(4);
            $table->string('mega_menu_style', 50)->nullable()->default('default');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('depth')->default(0);
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['menu_id', 'parent_id']);
            $table->index(['menu_id', 'sort_order']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
